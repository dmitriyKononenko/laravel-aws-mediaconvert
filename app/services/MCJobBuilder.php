<?php


namespace App\services;

use App\Output;
use App\OutputGroup;
use Illuminate\Support\Collection;

// Creates AWS Media Convert Job from required outputs params
class MCJobBuilder
{
    const QUEUE_REPLACEMENT = '#queue#';
    const ROLE_REPLACEMENT = '#role#';
    const NAME_REPLACEMENT = '#name#';
    const OUTPUT_GROUPS_REPLACEMENT = '#output_groups#';
    const INPUT_FILE_REPLACEMENT = '#input_file#';
    const OUTPUT_ID_INSERTION = '_OID';

    // Media Convert job config
    private $config = '';

    // AWS Job Queue ARN
    private $queue_arn;

    // AWS role ARN
    private $role_arn;

    // S3 bucket file path
    private $input_file;

    // S3 bucket for result files
    private $output_bucket;

    // Job name
    private $name;

    // Outputs list
    private $outputs;

    // Job base template
    private $template = '
        {
          "Queue": "'. self::QUEUE_REPLACEMENT .'",
          "Role": "'. self::ROLE_REPLACEMENT .'",
          "Name": "'. self::NAME_REPLACEMENT .'",
          "Settings": {
            "OutputGroups": ['. self::OUTPUT_GROUPS_REPLACEMENT .'],
            "AdAvailOffset": 0,
            "Inputs": [
              {
                "AudioSelectors": {
                  "Audio Selector 1": {
                    "Offset": 0,
                    "DefaultSelection": "DEFAULT",
                    "ProgramSelection": 1
                  }
                },
                "VideoSelector": {
                  "ColorSpace": "FOLLOW",
                  "Rotate": "DEGREE_0",
                  "AlphaBehavior": "DISCARD"
                },
                "FilterEnable": "AUTO",
                "PsiControl": "USE_PSI",
                "FilterStrength": 0,
                "DeblockFilter": "DISABLED",
                "DenoiseFilter": "DISABLED",
                "TimecodeSource": "EMBEDDED",
                "FileInput": "'. self::INPUT_FILE_REPLACEMENT .'"
              }
            ]
          },
          "AccelerationSettings": {
            "Mode": "DISABLED"
          },
          "StatusUpdateInterval": "SECONDS_60",
          "Priority": 0
        }
    ';

    /**
     * MCJobBuilder constructor.
     * @param string $queue_arn
     * @param string $role_arn
     * @param string $input_file
     * @param string $output_bucket
     * @param string $name
     */
    public function __construct(
        string $queue_arn,
        string $role_arn,
        string $input_file,
        string $output_bucket,
        string $name = 'Custom Job Template'
    )
    {
        $this->queue_arn = $queue_arn;
        $this->role_arn = $role_arn;
        $this->input_file = $input_file;
        $this->output_bucket = $output_bucket;
        $this->name = $name;
    }

    /**
     * @param Collection $outputs
     * @return MCJobBuilder
     */
    public function setOutputs(Collection $outputs): MCJobBuilder
    {
        $outputs->each(function($output) {
            if (!$output instanceof Output) {
                throw new \TypeError('some of values in $outputs array not Output instance');
            }
        });

        $this->outputs = $outputs;

        return $this;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        $output_groups_config = $this->buildOutputGroupsConfig();

        $replacements = [
            self::QUEUE_REPLACEMENT => $this->queue_arn,
            self::ROLE_REPLACEMENT => $this->role_arn,
            self::NAME_REPLACEMENT => $this->name,
            self::OUTPUT_GROUPS_REPLACEMENT => $output_groups_config,
            self::INPUT_FILE_REPLACEMENT => $this->input_file,
        ];

        foreach ($replacements as $replacement => $value) {
          $this->template = str_replace($replacement, $value, $this->template);
        }

        return json_decode($this->template, true);
    }

    /**
     * @return string
     */
    private function buildOutputGroupsConfig(): string
    {
        $grouped_outputs = $this->outputs->groupBy('output_group_id');

        $output_groups = OutputGroup::whereIn('id', $grouped_outputs->keys())->get();

        return $output_groups->map(function(OutputGroup $output_group) use ($grouped_outputs) {

            $output_config = $this->buildOutputConfig($grouped_outputs->get($output_group->id));

            return $this->buildOutputGroupConfig($output_group, $output_config);
        })->join(', ');
    }

    /**
     * Builds batch outputs config
     *
     * @param Collection $outputs
     * @return string
     */
    private function buildOutputConfig(Collection $outputs): string
    {
        // Insert output id in output file name
        return $outputs->map(function($output) {
            $config = json_decode($output->config, true);

            $insertion = self::OUTPUT_ID_INSERTION . $output->id;

            $config['NameModifier'] = $config['NameModifier'] . $insertion;

            return json_encode($config);
        })->join(', ');
    }

    /**
     * Builds config string for specified output group
     *
     * @param OutputGroup $output_group
     * @param string $outputs_config
     * @return string
     */
    private function buildOutputGroupConfig(OutputGroup $output_group, string $outputs_config): string
    {
        $file_name_extension = basename($this->input_file);

        $file_name = substr($file_name_extension, 0, strrpos($file_name_extension, '.'));

        $replacements = [
            OutputGroup::BUCKET_REPLACEMENT => $this->output_bucket,
            OutputGroup::FOLDER_REPLACEMENT => $output_group->slug,
            OutputGroup::OUTPUT_FILE_NAME_REPLACEMENT => $file_name,
            OutputGroup::OUTPUT_REPLACEMENT => $outputs_config,
        ];

        $result = $output_group->config;

        foreach ($replacements as $replacement => $value) {
            $result = str_replace($replacement, $value, $result);
        }

        return $result;
    }
}

<?php
/**
 * CustomerParadigm_AmazonPersonalize
 *
 * @category   CustomerParadigm
 * @package    CustomerParadigm_AmazonPersonalize
 * @copyright  Copyright (c) 2023 Customer Paradigm (https://customerparadigm.com/)
 * @license    https://github.com/Customer-Paradigm/Amazon-Personalize/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace CustomerParadigm\AmazonPersonalize\Model\Training;

use Aws\S3\S3Client;

class s3
{
    protected $nameConfig;
    protected $s3BucketName;
    protected $s3Client;
    protected $sdkClient;
    protected $region;
    protected $varDir;

    public function __construct(
        \CustomerParadigm\AmazonPersonalize\Model\Training\NameConfig $nameConfig,
        \CustomerParadigm\AmazonPersonalize\Api\AwsSdkClient $sdkClient
    ) {
        $this->nameConfig = $nameConfig;
        $this->region = $this->nameConfig->getAwsRegion();
        $this->s3BucketName = $this->nameConfig->buildName('personalize-s3bucket');
        $this->varDir = $this->nameConfig->getVarDir();
        $this->sdkClient = $sdkClient;
        $this->s3Client = $this->sdkClient->getClient('s3');
    }

    public function createS3Bucket()
    {
        try {
            $result = $this->s3Client->createBucket([
                'Bucket' => $this->s3BucketName,
                'CreateBucketConfiguration' => [
                    'LocationConstraint' => $this->region,
                ],
            ]);
        } catch (\Exception $e) {
            $this->nameConfig->getLogger('error')->error("\ncreate bucket error : \n" . $e->getMessage());
        }
        $this->nameConfig->saveName('s3BucketName', $this->s3BucketName);
        $this->addS3BucketPolicy();
        return $result;
    }

    public function addS3BucketPolicy()
    {
        $this->nameConfig->getLogger('info')->info("\nAdding bucket Policy to " . $this->s3BucketName);
        $this->s3Client->putBucketPolicy([
            'Bucket' => $this->s3BucketName,
            'Policy' => '{
            "Version": "2012-10-17",
            "Id": "PersonalizeS3BucketAccessPolicy",
            "Statement": [
                {
                    "Sid": "PersonalizeS3BucketAccessPolicy",
                    "Effect": "Allow",
                    "Principal": {
                        "Service": "personalize.amazonaws.com"
                    },
                    "Action": [
                        "s3:GetObject",
                        "s3:ListBucket",
                        "s3:PutObject"
                    ],
                    "Resource": [
                        "arn:aws:s3:::' . $this->s3BucketName . '",
                        "arn:aws:s3:::' . $this->s3BucketName . '/*"
                    ]
                }
             ]
            }',
        ]);
    }

    public function uploadCsvFiles()
    {
        $files = $this->getCsvFiles();
        foreach ($files as $type => $file) {
            $this->uploadFileToS3($type, $file);
        }
    }

    public function checkBucketExists()
    {
        $buckets = $this->listS3Buckets();
        foreach ($buckets['Buckets'] as $bucket) {
            if ($bucket['Name'] == $this->s3BucketName) {
                return true;
            }
        }
        return false;
    }

    public function listS3Buckets()
    {
        return $this->s3Client->listBuckets();
    }

    protected function uploadFileToS3($type, $filepath)
    {
        $data = file_get_contents($filepath);
        try {
            $this->s3Client->putObject([
                'ACL' => 'private',
                'Body' => $data,
                'Bucket' => $this->s3BucketName,
                'Key' => $type . ".csv",
            ]);
        } catch (\Exception $e) {
            $this->nameConfig->getLogger('error')->error("\nupload CSV files error : \n" . $e->getMessage());
        }
    }

    public function getCsvFiles()
    {
        $filenames = [];
        $csvDir = $this->varDir . "/export/amazonpersonalize/";
        $filelist = scandir($csvDir, SCANDIR_SORT_DESCENDING);
        foreach ($filelist as $item) {
            $breakout = explode('-', $item);
            $type = $breakout[0];
            if ($type == "." || $type == "..") {
                continue;
            }
            if (!array_key_exists($type, $filenames)) {
                $filenames[$type] = $csvDir . $item;
            }
        }
        return $filenames;
    }
}

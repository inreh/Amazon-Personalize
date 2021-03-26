<?php
namespace CustomerParadigm\AmazonPersonalize\Model\Training;

Use Aws\Personalize\PersonalizeClient;

class PersonalizeBase extends \Magento\Framework\Model\AbstractModel
{
	protected $nameConfig;
	protected $personalizeClient;
	protected $region;
	protected $varDir;
	protected $baseName;
	protected $apiCreate;
	protected $apiDescribe;

	public function __construct(
		\CustomerParadigm\AmazonPersonalize\Model\Training\NameConfig $nameConfig
	)
    	{
		$this->baseName = (new \ReflectionClass($this))->getShortName();
		$this->apiCreate = 'create' . $this->baseName . 'Async';
		$this->apiDescribe = 'describe' . $this->baseName;
		$this->nameConfig = $nameConfig;
		$this->region = $this->nameConfig->getAwsRegion();
		
		$this->personalizeClient = new PersonalizeClient(
			[ 'profile' => 'default',
			'version' => 'latest',
			'region' => "$this->region" ]
		);
	}

        public function checkAssetCreatedAndSync($type_name,$step_name,$name_value,$arn_value) {
                $rtn = false;
                $step_plural = $step_name . "s";
                if( $this->assetExists($step_plural,$name_value) ) {
                        $name = $type_name.$step_name;
                        $rtn = true;
                        if(empty($this->nameConfig->getConfigVal($name."Name"))) {
                                $this->nameConfig->saveName($name."Name", $name_value);
                        }
                        if(empty($this->nameConfig->getConfigVal($name."Arn"))) {
                                $this->nameConfig->saveArn($name."Arn", $arn_value);
                        }
                }
                return $rtn;
        }

        public function assetExists($type, $name) {
		try {
			$type = ucfirst($type);
                        $func_name = "list" . $type;
                        $assets = $this->personalizeClient->$func_name(array('maxResults'=>100));
                        if(empty($assets)) {
                                return false;
                        }
                        $type_key = array_key_first($assets->toArray());
                        foreach($assets[$type_key] as $idx=>$item) {
                                if($item['name'] === $name) {
                                        return true;
                                }
                        }
                } catch(Exception $e) {
                        $this->errorLogger->error( "\nassetExists() error. Message:\n" . print_r($e->getMessage(),true));
                        exit;
                }
                return false;
        }

}

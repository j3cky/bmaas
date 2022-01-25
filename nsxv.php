<?php
$nsxvurl = "https://172.16.12.203";
$userv = "admin";
$passwordv = "4dy0@pmR";

$edgeids = GetEdgeList();
//print_r($edgeids);
//GetArrayInterface($edgeids);
//GetNatList($edgeids)
//GetFWList($edgeids);
StoreEdgeService();
//GetLBVS($edgeids);
//GetLBPool($edgeids);
function GetEdgeList(){
	global $nsxvurl,$userv,$passwordv;
	$edgeids = array();
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "$nsxvurl/api/4.0/edges",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
	  CURLOPT_USERPWD => "$userv:$passwordv",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false
	));

	$response = curl_exec($curl);

	curl_close($curl);
	//echo $response;
        $edge = new SimpleXMLElement($response);
        $edgejson = json_encode($edge);
	$edgedecode = json_decode($edgejson);
	$count = count($edgedecode->edgePage->edgeSummary);
	$edgedata = $edgedecode->edgePage->edgeSummary;
	for($i=0;$i<$count;$i++){
		$edgeids[$i]=$edgedata[$i]->objectId;
	}
	return $edgeids;
}

function GetArrayInterface($edgeids){
        global $nsxvurl,$userv,$passwordv;
        $edgeid = array();
        $curl = curl_init();
	$interfacearr = array();
	$index=0;
	foreach($edgeids as $edgeid){
        	curl_setopt_array($curl, array(
          		CURLOPT_URL => "$nsxvurl/api/4.0/edges/$edgeid/vnics",
          		CURLOPT_RETURNTRANSFER => true,
          		CURLOPT_ENCODING => '',
          		CURLOPT_MAXREDIRS => 10,
          		CURLOPT_TIMEOUT => 0,
          		CURLOPT_FOLLOWLOCATION => true,
          		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          		CURLOPT_CUSTOMREQUEST => 'GET',
          		CURLOPT_USERPWD => "$userv:$passwordv",
          		CURLOPT_SSL_VERIFYHOST => false,
          		CURLOPT_SSL_VERIFYPEER => false
        	));

        	$response = curl_exec($curl);

        	//echo $response;
        	$interface = new SimpleXMLElement($response);
        	$interfacejson = json_encode($interface);
        	$interfacedecode = json_decode($interfacejson);
		//print_r($interfacedecode);
        	$count = count($interfacedecode->vnic);
		$interfacedata = $interfacedecode->vnic;
        	for($i=0;$i<$count;$i++){
                	$status=$interfacedata[$i]->isConnected;
			$type=$interfacedata[$i]->type;
			if($status == "true" && $type=="internal"){
				//echo $interfacedata[$i]->portgroupName;
				$interfacearr[$index]['segmentname']=$interfacedata[$i]->portgroupName;
				$interfacearr[$index]['ipgateway']=$interfacedata[$i]->addressGroups->addressGroup->primaryAddress;	
				$interfacearr[$index]['prefix']=$interfacedata[$i]->addressGroups->addressGroup->subnetPrefixLength;
				$index++;
			}
        	}
        	//return edgeid;
		//return $interfacearr;
	}
	curl_close($curl);
	return $interfacearr;
}
function GetNatList($edgeids){
	global $nsxvurl,$userv,$passwordv;
	$curl = curl_init();
	$index=0;
	$natarr = array();
	foreach($edgeids as $edgeid){
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "$nsxvurl/api/4.0/edges/$edgeid/nat/config",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_USERPWD => "$userv:$passwordv",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false
	));

	$response = curl_exec($curl);
        $nat = new SimpleXMLElement($response);
        $natjson = json_encode($nat);
        $natdecode = json_decode($natjson);
	//print_r($natdecode->natRules->natRule);
	if(isset($natdecode->natRules->natRule)){
		//$count = count($natdecode->natRules->natRule);
		if(is_array($natdecode->natRules->natRule)){
			foreach($natdecode->natRules->natRule as $key => $row){
				$natarr[$index]['translatedAddress'] = $row->translatedAddress;
				$natarr[$index]['originalAddress'] = $row->originalAddress;
				$natarr[$index]['action'] = $row->action;
				$natarr[$index]['originalPort'] = $row->originalPort;
				$natarr[$index]['translatedPort'] = $row->translatedPort;
				$natarr[$index]['ruleId'] = $row->ruleId;
				$index++;
			}
			//echo "test";
		}
		else{
			//echo "test2";
                        $natarr[$index]['translatedAddress'] = $row->translatedAddress;
                        $natarr[$index]['originalAddress'] = $row->originalAddress;
                        $natarr[$index]['action'] = $row->action;
                        $natarr[$index]['originalPort'] = $row->originalPort;
                        $natarr[$index]['translatedPort'] = $row->translatedPort;
			$natarr[$index]['ruleId'] = $row->ruleId;
                        $index++;
			//echo $natdecode->natRules->natRule->translatedAddress;	
		}

	}
	//echo $response;
	}
	curl_close($curl);
	return $natarr;


}
function GetFWList($edgeid){
	global $nsxvurl,$userv,$passwordv;
	$fwarr =  array();
	$curl = curl_init();
	//foreach($edgeids as $edgeid){	
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "$nsxvurl/api/4.0/edges/$edgeid/firewall/config/",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
	          CURLOPT_USERPWD => "$userv:$passwordv",
	          CURLOPT_SSL_VERIFYHOST => false,
	          CURLOPT_SSL_VERIFYPEER => false
		));

		$response = curl_exec($curl);
		//echo $response;	
		//curl_close($curl);
	        $fw = new SimpleXMLElement($response);
	        $fwjson = json_encode($fw);
	       $fwdecode = json_decode($fwjson);

		$fwresults = $fwdecode->firewallRules->firewallRule;
		return $fwresults;
		//foreach($fwresults as $key => $row){
		//	if($row->ruleType == "user"){
				//echo "user";
		//	}
		//}
	//}
	curl_close($curl);

}
function StoreEdgeService(){
	global $nsxvurl,$userv,$passwordv;
	$curl = curl_init();
	$index=0;
	$servicearr = array();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://172.16.12.203/api/2.0/services/application/scope/globalroot-0',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_USERPWD => "$userv:$passwordv",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false
	));

	$response = curl_exec($curl);
        $service = new SimpleXMLElement($response);
        $servicejson = json_encode($service);
        $servicedecode = json_decode($servicejson);
	$results=$servicedecode->application;
	foreach($results as $key => $row){
		if(isset($row->element->value)){
			$servicearr[$index]['id']=$row->objectId;
			$servicearr[$index]['dest_port']=$row->element->value;
			$servicearr[$index]['name']=$row->name;
			$index++;
			//echo $row->objectId." ".$row->element->value." ".$row->name. "\n";
			
		}
	}
	curl_close($curl);
	return $servicearr;	
	//$index_search =  array_search('22',array_column($servicearr,'id'));
        //print_r($servicearr[$index_search]);
}
function GetLBPool($edgeids){
	 global $nsxvurl,$userv,$passwordv;
	$curl = curl_init();
	$poolarr= array();
	$index = 0;
	//$indexm = 0;
	foreach($edgeids as $edgeid){
		//$indexm=0;
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "$nsxvurl/api/4.0/edges/$edgeid/loadbalancer/config",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
	          CURLOPT_USERPWD => "$userv:$passwordv",
	          CURLOPT_SSL_VERIFYHOST => false,
	          CURLOPT_SSL_VERIFYPEER => false
		));
	
		$response = curl_exec($curl);
	        $pool = new SimpleXMLElement($response);
	        $pooljson = json_encode($pool);
	        $pooldecode = json_decode($pooljson);
		if(isset($pooldecode->pool)){
			$result = $pooldecode->pool;
			foreach($result as $key => $row){
				$poolarr[$index]['name']= $row->name;
				$poolarr[$index]['poolId']= $row->poolId;
				$poolarr[$index]['monitorId']= $row->monitorId;
				$poolarr[$index]['algorithm']= $row->algorithm;
				$members=$row->member;
				$indexm=0;
				foreach($members as $keymen => $rowmem){
					$poolarr[$index]['member'][$indexm]['ipAddress']=$rowmem->ipAddress;
					$poolarr[$index]['member'][$indexm]['port']=$rowmem->port;
					$poolarr[$index]['member'][$indexm]['monitorPort']=$rowmem->monitorPort;
					$indexm++;
				}
				$index++;
			}
		}
		//print_r($pooldecode);
	}
	curl_close($curl);
	return $poolarr;	
}
function GetLBVS($edgeids){
         global $nsxvurl,$userv,$passwordv;
        $curl = curl_init();
        $vsarr= array();
        $index = 0;
        //$indexm = 0;
        foreach($edgeids as $edgeid){
                //$indexm=0;
                curl_setopt_array($curl, array(
                  CURLOPT_URL => "$nsxvurl/api/4.0/edges/$edgeid/loadbalancer/config",
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'GET',
                  CURLOPT_USERPWD => "$userv:$passwordv",
                  CURLOPT_SSL_VERIFYHOST => false,
                  CURLOPT_SSL_VERIFYPEER => false
                ));
                $response = curl_exec($curl);
                $vs = new SimpleXMLElement($response);
                $vsjson = json_encode($vs);
                $vsdecode = json_decode($vsjson);
		//print_r($vsdecode->virtualServer);
                if(isset($vsdecode->virtualServer)){
                        $result = $vsdecode->virtualServer;
                        foreach($result as $key => $row){
                                $vsarr[$index]['name']= $row->name;
                                $vsarr[$index]['ipAddress']= $row->ipAddress;
                                $vsarr[$index]['protocol']= $row->protocol;
				$vsarr[$index]['defaultPoolId']= $row->defaultPoolId;
				$vsarr[$index]['port']= $row->port;
                                $index++;

                        }
                }
                //print_r($pooldecode);
        }
        curl_close($curl);
        return $vsarr;

}
?>

<?php
include "nsxv.php";
$nsxurl = "https://172.16.12.205";
$user="admin";
$password="4dy0@pmR4dy0@pmR";

$edgeids =  GetEdgeList();
//$segmentlists = GetArrayInterface($edgeids);
$tier1 = GetTier1();
$tier0 = GetTier0();
CreateLBService($tier1);
//echo $tier1;
//CreateSegment($segmentlists,$tier1);
//$natarr = GetNatList($edgeids);
//print_r($natarr);
//CreateNAT($natarr,$tier1);
//CreateFW($edgeids);
//StoreService();
//CreateLBPool($edgeids);
//CreateLBVS($edgeids);
CreateFWRule($edgeids,$tier0);
function GetTZProfile(){
        global $nsxurl,$user,$password;
        $edgeids = array();
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "$nsxurl/policy/api/v1/infra/transport-zone-profiles/",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_USERPWD => "$user:$password",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;
        //$tzprofile = new SimpleXMLElement($response);
        //$tzprofilejson = json_encode($tzprofile);
        $tzprofiledecode = json_decode($response);
	//print_r($tzprofiledecode);
        /*$count = count($tzprofiledecode->edgePage->edgeSummary);
        $edgedata = $edgedecode->edgePage->edgeSummary;
        for($i=0;$i<$count;$i++){
                $edgeids[$i]=$edgedata[$i]->objectId;
        }*/
	return $tzprofiledecode->results[0]->path;
}

function GetTier1(){
	global $nsxurl,$user,$password;
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "$nsxurl/policy/api/v1/infra/tier-1s",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_USERPWD => "$user:$password",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false
	
	));

	$response = curl_exec($curl);
	$t1decode = json_decode($response);
	curl_close($curl);
	return $t1decode->results[0]->path;
}
function GetTier0(){
        global $nsxurl,$user,$password;
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "$nsxurl/policy/api/v1/infra/tier-0s",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_USERPWD => "$user:$password",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false

        ));

        $response = curl_exec($curl);
        $t1decode = json_decode($response);
        curl_close($curl);
        return $t1decode->results[0]->path;
}

function CreateSegment($segmentlists,$tier1){
        global $nsxurl,$user,$password;
        $edgeids = array();
        $curl = curl_init();
	foreach($segmentlists as $key => $row){
		$segment = $row['segmentname'];
		$ipgateway = $row['ipgateway'];
		$prefix = $row['prefix'];
		echo '{
                            "display_name":"'.$segment.'",
                            "subnets": [
                              {
                                "gateway_address": "'.$ipgateway.'/'.$prefix.'"
                              }
                            ],
                            "connectivity_path": "'.$tier1.'"
                          }';
		 curl_setopt_array($curl, array(
          		CURLOPT_URL => "$nsxurl/policy/api/v1/infra/segments/$segment",
		          CURLOPT_RETURNTRANSFER => true,
		          CURLOPT_ENCODING => '',
		          CURLOPT_MAXREDIRS => 10,
		          CURLOPT_TIMEOUT => 0,
		          CURLOPT_FOLLOWLOCATION => true,
		          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		          CURLOPT_CUSTOMREQUEST => 'PATCH',
		          CURLOPT_USERPWD => "$user:$password",
		          CURLOPT_SSL_VERIFYHOST => false,
		          CURLOPT_SSL_VERIFYPEER => false,
			  CURLOPT_POSTFIELDS =>'{
			    "display_name":"'.$segment.'",
			    "subnets": [
			      {
			        "gateway_address": "'.$ipgateway.'/'.$prefix.'"
			      }
			    ],
			    "connectivity_path": "'.$tier1.'"
			  }',
			 CURLOPT_HTTPHEADER => array(
			    'Content-Type: application/json'
  			 ),
	        ));

        	$response = curl_exec($curl);
		echo $response;
	}
	curl_close($curl);
	/*
        curl_setopt_array($curl, array(
          CURLOPT_URL => "$nsxurl/policy/api/v1/infra/segments/web-tier",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PATCH',
          CURLOPT_USERPWD => "$user:$password",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $tzprofiledecode = json_decode($response);
        return $tzprofiledecode->results[0]->path;
	*/
}
function CreateNAT($natarr,$tier1){
	global $nsxurl,$user,$password;
	$curl = curl_init();
	foreach($natarr as $key => $row){
	//CreateService($row['translatedPort']);
	//print_r($row['translatedAddress']);
		if ($row['action'] == "dnat"){
			CreateService($row['translatedPort']);
			$valuenat = '{
		                   "display_name" : "'.$row['ruleId'].'",
		                   "description" : "'.$row['ruleId'].'",		     
				     "action" : "DNAT",
                   		     "destination_network" : "'.$row['originalAddress'].'",
				     "service": "/infra/services/'.$row['translatedPort'].'",
		                   "translated_network" : "'.$row['translatedAddress'].'",
             			    "translated_ports" : "'.$row['originalPort'].'",
                		   "enabled" : true,
		                   "logging" : false,
		                   "firewall_match" : "MATCH_EXTERNAL_ADDRESS",
		                   "_revision" : 0
				}
                                    ';
		}elseif ($row['action'] == "snat"){
                        $valuenat = '
				{
                                   "display_name" : "'.$row['ruleId'].'",
                                   "description" : "'.$row['ruleId'].'",
				     "action" : "SNAT",
                                     "source_network" : "'.$row['originalAddress'].'",
				     "service": "",
                   "translated_network" : "'.$row['translatedAddress'].'",

                   "enabled" : true,
                   "logging" : false,
                   "firewall_match" : "MATCH_EXTERNAL_ADDRESS",
                   "_revision" : 0
				}
				    ';
		}	
		echo $valuenat;	
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "$nsxurl/policy/api/v1$tier1/nat/USER/nat-rules/$row[ruleId]",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'PATCH',
	          CURLOPT_USERPWD => "$user:$password",
	          CURLOPT_SSL_VERIFYHOST => false,
	          CURLOPT_SSL_VERIFYPEER => false,
		  CURLOPT_POSTFIELDS => $valuenat,
		  CURLOPT_HTTPHEADER => array(
		    'Content-Type: application/json',
		  ),
		));

		$response = curl_exec($curl);
		
	}
	curl_close($curl);
	echo $response;

}
function CreateService($service){
	global $nsxurl,$user,$password;
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "$nsxurl/policy/api/v1/infra/services/$service",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'PATCH',
          CURLOPT_USERPWD => "$user:$password",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false,
	  CURLOPT_POSTFIELDS =>'{
		  "display_name": "'.$service.'",
		  "service_entries": [
	      		{
          		"resource_type": "L4PortSetServiceEntry",
		        "display_name": "'.$service.'",
  		        "destination_ports": [
              			"'.$service.'"
          		],
          		"l4_protocol": "TCP"
      			}
  		]
	}',
	  CURLOPT_HTTPHEADER => array(
	    'Content-Type: application/json',
	  ),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	//echo $response;
}
function CreateFWPolicy($edgeids){
	$curl = curl_init();
	global $nsxurl,$user,$password;
	foreach($edgeids as $edgeid){
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "$nsxurl/policy/api/v1/infra/domains/default/gateway-policies/$edgeid",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'PATCH',
          CURLOPT_USERPWD => "$user:$password",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false,
	  CURLOPT_POSTFIELDS =>'{
	    "rules": [],
	    "resource_type": "GatewayPolicy",
	    "display_name": "'.$edgeid.'",
	    "parent_path": "/infra/domains/default",
	    "category": "LocalGatewayRules"
	}',
	  CURLOPT_HTTPHEADER => array(
    		'Content-Type: application/json',
	  ),
	));

	$response = curl_exec($curl);
	}
	curl_close($curl);
	echo $response;	
}
function CreateFWRule($edgeids,$tier0){
	global $nsxurl,$user,$password;
	$index=0;
	CreateFWPolicy($edgeids);
	$edgetservice = StoreService();
	$edgevservice = StoreEdgeService();
	$numcheck = true;
	//$edgeids = GetEdgeList();
	foreach ($edgeids as $edgeid){
		//echo $edgeid;
		$fwresults = GetFWList($edgeid);
		//print_r($fwresults);
                foreach($fwresults as $key => $row){
                      if($row->ruleType == "user"){
				
                                //print_r($row);
				//echo $index;
				$valpatch="";
				$index++;
				if(isset($row->source)){
					if(is_array($row->source->ipAddress))
					{	
						$sindex=1;
						$lindex=count($row->source->ipAddress);
						$sources = $row->source->ipAddress;
						$valpatch .='"source_groups": [';
						foreach($sources as $source){
							//echo $source;
							if ( $sindex != $lindex){
								$valpatch .=  '"'.$source.'",';
							}else{
								$valpatch .= '"'.$source.'"';
							}
							$sindex++;
						}
						$valpatch .='],';
						//echo $valpatch;
					}else{
                                                        $valpatch .= '"source_groups": ["'.$row->source->ipAddress.'"],';
					}


				}else{
					$valpatch .= '"source_groups": ["any"],';
				}

				if(isset($row->destination)){
					if(is_array($row->destination->ipAddress)){
                                        	//print_r($row->destination->ipAddress);
                                                $sindex=1;
                                                $lindex=count($row->destination->ipAddress);
                                                $dests = $row->destination->ipAddress;
						$valpatch .='"destination_groups": [';
                                                foreach($dests as $dest){
                                                        //echo $source;
                                                        if ( $sindex != $lindex){
                                                                $valpatch .=  '"'.$dest.'",';
                                                        }else{
                                                                $valpatch .= '"'.$dest.'"';
                                                        }
                                                        $sindex++;
                                                }
						$valpatch .='],';
                                                //echo $valpatch;
					}else{
						$valpatch .= '"destination_groups": ["'.$row->destination->ipAddress.'"],';
					}
                                }else{
					$valpatch .= '"destination_groups": ["any"],';
				}
				if(isset($row->application)){
                                        //print_r($row);
					//echo "AppTESTTTTTTTTTTTTT";
					//$valpatch .='"services": [';
                                        if(is_array($row->application->applicationId)){
                                                //print_r($row->application->applicationId);
						//$index_search =  array_search('22',array_column($edgevservice,'id'));
                                                $sindex=1;
                                                $lindex=count($row->application->applicationId);
                                                $apps = $row->application->applicationId;
						//print_r($apps);
                                                //$valpatch .='"services": [';
                                                foreach($apps as $app){
                                                        //echo $source;
							$index_search_v =  array_search($app,array_column($edgevservice,'id'));
                                                        $index_search_t =  array_search($edgevservice[$index_search_v]['dest_port'],array_column($edgetservice,'dest_port'));
							if(is_numeric($edgevservice[$index_search_v]['dest_port']))
							{
								if($sindex == 1)$valpatch .='"services": [';
                                                        	if ( $sindex != $lindex){
                                                                	$valpatch .=  '"'.$edgetservice[$index_search_t]['path'].'",';
                                                        	}else{
                                                                	$valpatch .= '"'.$edgetservice[$index_search_t]['path'].'"],';
                  	                                      	}
								//$valpatch .='],';					
								$numcheck = true;
								$sindex++;
							}else{
								$numcheck = false;
							}
                                                }
						
                                                //$valpatch .='],';
						//echo $valpatch;
                                        }else{
						$app = $row->application->applicationId;
						$index_search_v =  array_search($app,array_column($edgevservice,'id'));
						$index_search_t =  array_search($edgevservice[$index_search_v]['dest_port'],array_column($edgetservice,'dest_port'));
						if(is_numeric($edgevservice[$index_search_v]['dest_port'])){
                                                	$valpatch .= '"services": ["'.$edgetservice[$index_search_t]['path'].'"],';
						}
						//print_r($row->application->applicationId);
                                        }
					//$valpatch .='],';
					//$valpatch .= '"action":"'.$row->action.'"}';
                                }
				//$valpatch .= '"action":"'.$row->action.'"}';
					if($row->action == "accept") $action = "ALLOW";
					if($row->action == "deny") $action = "DROP";
					if($row->action == "reject") $action = "REJECT";
				    	$datapatch = '{     
   	 				"logged": false,
					"scope": [
      						"'.$tier0.'"
    					],
    					'.$valpatch.'
    					"action":"'.$action.'"
					}';
				if(strpos($datapatch,'services')!== false){
					echo $datapatch;
//====================================Start Curl===================================================
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "$nsxurl/policy/api/v1/infra/domains/default/gateway-policies/$edgeid/rules/$row->name",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'PATCH',
  CURLOPT_POSTFIELDS =>$datapatch,
          CURLOPT_USERPWD => "$user:$password",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Content-Type: application/json',
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;




//======================================End Curl================================================
				}
                      }
                }
		//if($fwresult->ruleType == "user"){
		//	echo "test";
		//}
		//print_r($fwresult);
	}
	
}
function StoreService(){
	global $nsxurl,$user,$password;
	$curl = curl_init();
	$servicearr=array();	
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "$nsxurl/policy/api/v1/infra/services",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_USERPWD => "$user:$password",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false
	));

	$response = curl_exec($curl);

	curl_close($curl);
	//echo $response;	
	$servicedecode = json_decode($response);
	$result = $servicedecode->results;
	//print_r($result);
	$index=0;
	foreach($result as $key => $row){
		if(isset($row->service_entries[0]->destination_ports)){
			//print_r($row->service_entries[0]->destination_ports);
			if(count($row->service_entries[0]->destination_ports)<=1){
				//echo "test";
				$servicearr[$index]['id']=$row->id;
				$servicearr[$index]['dest_port']=$row->service_entries[0]->destination_ports[0];
				$servicearr[$index]['path']=$row->path;
				$index++;
			}	
		}
	}
	$index_search =  array_search('22',array_column($servicearr,'dest_port'));
	//print_r($servicearr[$index_search]);
	
	return $servicearr;
	
}
function CreateLBPool($edgeids){
	global $nsxurl,$user,$password;
	$curl = curl_init();
	$pools = GetLBPool($edgeids);
	//print_r($pools);
	$sindex = 0;
	foreach($pools as $key => $row){
	//print_r($row);
	$members = $row['member'];	
	$monid=$row['monitorId'];
	echo $monid;
	if($monid=="monitor-1"){
		$mon="/infra/lb-monitor-profiles/default-tcp-lb-monitor";
	}
	elseif($monid=="monitor-2"){
		$mon="/infra/lb-monitor-profiles/default-http-lb-monitor";
	}elseif($monid=="monitor-3"){
		$mon="/infra/lb-monitor-profiles/default-https-lb-monitor";
	}
	echo $mon;
	$sindex = 1;
	$lindex=count($members); 
	//print_r($members);
	$valpost = '{
            "algorithm": "ROUND_ROBIN",
	    "members": [';
		foreach ($members as $keymem => $rowmem){
			if($sindex != $lindex){
			$valpost .='                {
                    "display_name": "VM4-Cluster1",
                    "ip_address": "'.$rowmem['ipAddress'].'",
                    "port": "80",
                    "admin_state": "ENABLED",
                    "backup_member": false,
                    "weight": 1
                },';}else{
                        $valpost .='                {
                    "display_name": "VM4-Cluster1",
                    "ip_address": "'.$rowmem['ipAddress'].'",
                    "port": "80",
                    "admin_state": "ENABLED",
                    "backup_member": false,
                    "weight": 1
                }';
		
		}
		$sindex++;
		}
	$valpost .='],
            "active_monitor_paths": [
                "'.$mon.'"
            ]
        }';
	echo $valpost;
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "$nsxurl/policy/api/v1/infra/lb-pools/$row[name]",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_USERPWD => "$user:$password",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false,
	  CURLOPT_CUSTOMREQUEST => 'PATCH',
	  CURLOPT_POSTFIELDS =>$valpost,
	  CURLOPT_HTTPHEADER => array(
	    'Content-Type: application/json'
	  ),
	));

	$response = curl_exec($curl);
	}

	curl_close($curl);
	//echo $response;

}
function CreateLBService($tier1){
	 global $nsxurl,$user,$password;
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "$nsxurl/policy/api/v1/infra/lb-services/lb",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'PATCH',
	  CURLOPT_USERPWD => "$user:$password",
	  CURLOPT_SSL_VERIFYHOST => false,
	  CURLOPT_SSL_VERIFYPEER => false,
	  CURLOPT_POSTFIELDS =>' {
	     "resource_type": "LBService",
	     "enabled": true,
	     "size":"SMALL",
	     "connectivity_path": "'.$tier1.'"
	 }',
	  CURLOPT_HTTPHEADER => array(
	    'Content-Type: application/json'
	  ),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	//echo $response;
}
function CreateLBVS($edgeids){
	global $nsxurl,$user,$password;
	$curl = curl_init();
	$vsresults = GetLBVS($edgeids);
	$pools = GetLBPool($edgeids);
	foreach($vsresults as $key => $row){
		//print_r($row);
		$index_search =  array_search($row['defaultPoolId'],array_column($pools,'poolId'));
		echo $pools[$index_search]['name'];
		$port = str_replace(',','","',$row['port']);
		$portval='"'.$port.'"';
		echo $portval;
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "$nsxurl/policy/api/v1/infra/lb-virtual-servers/$row[name]",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'PATCH',
          CURLOPT_USERPWD => "$user:$password",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false,
	  CURLOPT_POSTFIELDS =>'{
		"resource_type": "LBVirtualServer",
		"ip_address":"'.$row['ipAddress'].'",
		"ports": ['.$portval.'],
		"application_profile_path": "/infra/lb-app-profiles/default-tcp-lb-app-profile",
	            "lb_service_path": "/infra/lb-services/lb",
	            "pool_path": "/infra/lb-pools/'.$pools[$index_search]['name'].'"
	
	}',
	  CURLOPT_HTTPHEADER => array(
	    'Content-Type: application/json'
	  ),
	));

	$response = curl_exec($curl);
	echo $response;
	}
	curl_close($curl);
	//echo $response;
}
?>

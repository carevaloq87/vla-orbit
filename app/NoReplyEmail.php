<?php
namespace App;

use Illuminate\Support\Facades\Mail;

use Auth;
use App\ServiceProvider;
use App\Mail\NoReplyEmailMailable;
use Helpers;

class NoReplyEmail
{
	public $client;

	function __construct() 
	{
	       $this->client = (new \App\Repositories\VlaSoap)->ws_no_reply_emails_init();
	}

	public function getAllTemplates()
	{
		try 
		{
			$user = auth()->user();			
			$response =  $this->client->GetAllTemplates()
									  ->GetAllTemplatesResult;			
			return json_decode(json_encode($response->EmailTemplates),true);
		} 
		catch (Exception $e) 
		{
			return array( 'success' => 'error' , 'message' => 'Ups, something went wrong.' );			
		}
	}

	public function getTemplateById( $template_id )
	{
		try 
		{
			$templates = self::getAllTemplates();

			foreach ($templates as $template) 
			{
				if( $template['RefNo'] == $template_id )
				{
					return $template;
				}
			}
			return false;
		} 
		catch (Exception $e) 
		{
			return array( 'success' => 'error' , 'message' => 'Ups, something went wrong.' );		
		}
	}

	public function getAllTemplatesBySection()
	{
		try 
		{
			$data = [];			
			if( auth()->user()->sp_id != 0)
			{				
				$section = self::getSection();			
				$info = [ 'Section' => $section ];
				$response = $this->client->GetTemplatesBySection( $info );
				$templates = json_decode(json_encode($response->GetTemplatesBySectionResult->EmailTemplates),true);
				foreach ($templates as $template) 
				{
					$user = User::find($template['CreatedBy']);					
					if($template['RefNo'] > 0 )
					{
						if(isset($user))
						{
							$tempLog = array('UserSp'=>$user->sp_id);					
							$template = array_merge($template, $tempLog);	
						}
						$data[] = $template;						
					}
				}
			}
			else
			{
				$templates = self::getAllTemplates();
				array_shift( $templates ); // Remove first element of array as it is returning an empty element
				$data = $templates;
			}
			usort($data, function($a, $b){ return strcasecmp($b["Section"], $a["Section"]); });
			return ['data' => $data ];
		} 
		catch (Exception $e) 
		{
			return array( 'success' => 'error' , 'message' => 'Ups, something went wrong.' );		
		}
	}

	public function getAllMailBoxes()
	{
		try 
		{
			$response = $this->client->GetAllMailBoxesasJSON();
			return json_decode( $response->GetAllMailBoxesasJSONResult, true );
		} 
		catch (Exception $e) 
		{
			return array( 'success' => 'error' , 'message' => 'Ups, something went wrong.' );		
		}
	}

	public function sendEmail( $email_data )
	{	
		try 
		{
	        // Current time
	        $date_now  = date("Y-m-d");
	        $time_now  = date("H:i:s");
	        $date_time = $date_now . "T" . $time_now;
	       	$files = array();

	       	$attachment_index = 0;	       	
	       	while(isset($email_data['attachment'.$attachment_index]))
	       	{	       		
	       		$files[] = $email_data['attachment'.$attachment_index];
	       		$attachment_index++;
	       	}	       			
	       	if( isset($email_data['mainAttachment']) )
	       	{	       		       		
	       		$files[] = $email_data['mainAttachment'];
	       	}	       		       	
	       	$attachments = self::attachFiles( $files );	   	
		   	$sp_name = '';
		   	$sp_contact = '';
		   	$suffix = '<br><hr>';
	   		if( auth()->user()->sp_id != 0)
			{
		   		$sp_obj = new ServiceProvider();
		   		$service_provider = $sp_obj->getServiceProviderByID( auth()->user()->sp_id );
		   		$service_provider = json_decode($sp_obj->getServiceProviderByID( auth()->user()->sp_id )['data'])[0];
		   		$sp_name = $service_provider->ServiceProviderName;
		   		$suffix .= '<em>If you wish to contact us, please do not reply to this message. Replies to this message will not be read or responded to.</em><br><br>';
		   		$sp_contact .= 'To contact us:<br><br>';
		   		$sp_contact .= $sp_name . '<br>';
		   		/* Temp disabled
		   		if( $service_provider->ContactPhone != '#')
		   		{
		   			$sp_contact .= $service_provider->ContactPhone . '<br>';
		   		}
		   		*/
		   		if( $service_provider->ServiceProviderURL != '#')
		   		{
		   			$sp_contact .= $service_provider->ServiceProviderURL . '<br>';
		   		}
		   		$suffix .= $sp_contact;
	   		}

	       	$prefix = '<em>This email was sent by ' . $sp_name . ' to ' . $email_data['to'] .  ' </em><br><em>Please do not reply to this email.</em><br><hr><br>';

		   	$suffix .= '<br><p classname = "orbitprefix" style="background: #f5f8fa; padding-top: 15px;box-sizing: border-box; color: #aeaeae; font-size: smaller; text-align: center; margin:0px">© 2018 Orbit. All rights reserved.</p><p classname = "emailprefix" style=" background: #f5f8fa; padding: 15px;box-sizing: border-box; color: #74787e;line-height: 1.4; margin: 0px; font-size: small;">Disclaimer: The material in this email is a general guide only. It is not legal advice. The law changes all the time and the general information in this email may not always apply to your own situation. The information in this email has been carefully collected from reliable sources. The sender is not responsible for any mistakes or for any decisions you may make or action you may take based on the information in this email. Some links in this email may connect to websites maintained by third parties. The sender is not responsible for the accuracy or any other aspect of information contained in the third-party websites. This email is intended for the use of the person or organisation it is addressed to and must not be copied, forwarded or shared with anyone without the sender’s consent (agreement). If you are not the intended recipient (the person the email is addressed to), any use, sharing, forwarding or copying of this email and/or any attachments is strictly prohibited. If you received this e-mail by mistake, please let the sender know and please destroy the original email and its contents.</p><br><br>';
		   	
		   	$is_clc =  in_array( \App\Http\helpers::getRole(), ['CLC', 'AdminSpClc']) ;
		   	$fromAddress = env('MAIL_FROM_ADDRESS', 'hello@example.com'); 
        	if( $is_clc )
        	{
        		$fromAddress = env('MAIL_FROM_ADDRESS_CLC', 'hello@example.com');
        	}

	       	$email_data['message'] = $prefix . $email_data['message'] . $suffix;
	       	$info = [
						'MessageObject' => [
												'Attachments' 	=> $attachments,
												'Body' 			=> $email_data['message'],
												'Deliverd' 		=> 1,
												'Error' 		=> 0,
												'FromAddress' 	=> $fromAddress,
												'PersonID' 		=> auth()->user()->id,
												'RefNo' 		=> 0,
												'Section' 		=> self::getSection(),
												'SentOn' 		=> $date_time,
												'Subject' 		=> $email_data['subject'],
												'ToAddress' 	=> $email_data['to'],
											],
						'IsHTML'		=> true,
					];
			$email_data['attachments'] = $attachments;
			$response = $this->client->SendEmailasJSON($info);
			Mail::to( auth()->user()->email )->send( new NoReplyEmailMailable( $email_data ) );
			//return json_decode( $response->SendEmailasJSONResult, true );
			return array( 'success' => 'success' , 'message' => 'The email was sent.' );
		} 
		catch (Exception $e) 
		{
			return array( 'success' => 'error' , 'message' => 'Ups, something went wrong.' );
		}
	}

    public function attachFiles( $files )
    {
    	$attachments = [];

    	foreach ($files as $file) {
    		//$file = $current_file['files'];
    		$handle = fopen($file->getPathName(), "rb");                  // Open the temp file

	       	$content = fread( $handle, filesize($file->getPathName()) );  // Read the temp file

	       	fclose($handle);

	       	$attachments[] = [ 'AttachmentBytes' => $content, 'FileName' => $file->getClientOriginalName() ];
	       	
    	}
    	return $attachments;
    }
    
	public function saveEmailTemplate( $data )
	{
		try 
		{	
	        // Current time
	        $date_now  = date("Y-m-d");
	        $time_now  = date("H:i:s");
	        $date_time = $date_now . "T" . $time_now;

		   	$section = '';

		   	if( isset($data['Section']) && $data['Section'] != '' && !isset($data['all']) )
		   	{
		   		$section = $data['Section'];
		   	}
		   	elseif( isset($data['all']) && $data['all'] == 'on' )
		   	{
		   		$section = 'All';
		   	}
		   	else
		   	{
		   		$section = self::getSection();
		   	}

			$template =  [
								'RefNo' 		=> $data['RefNo'],
								'Created' 		=> $date_time,
								'CreatedBy' 	=> auth()->user()->id,
								'Name' 			=> $data['name'],
								'Section' 		=> $section,
								'Subject' 		=> $data['subject'],
								'TemplateText' 	=> $data['template'],
								'Updated' 		=> $date_time,
								'UpdatedBy' 	=> auth()->user()->id,
						   ];

       		$info = [ 'ObjectInstance' => $template ];
       		$response = $this->client->SaveEmailTemplate( $info );

			return array( 'success' => 'success' , 'message' => 'The template was saved.', 'data' => $response );
			
		} 
		catch (Exception $e) 
		{
			return array( 'success' => 'error' , 'message' => 'Ups, something went wrong.' );		
		}
	}
	public function saveFromAddress()
	{
		try {
	        // Current time
	        $date_now  = date("Y-m-d");
	        $time_now  = date("H:i:s");
	        $date_time = $date_now . "T" . $time_now;
/*
			$address =  [
								'RefNo' 		=> 0,
								'Created' 		=> $date_time,
								'CreatedBy' 	=> auth()->user()->name,
								'Code' 			=> $data['code'],								
								'Value' 		=> $data['value'],
								'Updated' 		=> $date_time,
								'UpdatedBy' 	=> auth()->user()->name,
						   ];*/
			$address =  [
								'RefNo' 		=> 0,
								'Created' 		=> $date_time,
								'CreatedBy' 	=> auth()->user()->name,
								'Code' 			=> 'test',								
								'Value' 		=> 'test@test.com',
								'Updated' 		=> $date_time,
								'UpdatedBy' 	=> auth()->user()->name,
						   ];

       		$info = [ 'ObjectInstance' => $address ];
       		$response = $this->client->SaveFromAddress( $info );
			
		} 
		catch (Exception $e) 
		{
			return array( 'success' => 'error' , 'message' => 'Ups, something went wrong.' );		
		}
	}

	public function getSection()
	{
	   	$section = 'All';
		/*	   	
	   	//Simple SAML user provides department attribute
	   	if ( session('login_vla_attributes') && isset( session('login_vla_attributes')['department'][0]) )
	   	{
	   		$section = session('login_vla_attributes')['department'][0];
	   	}
	   	else 
	   	{		   	*/	
   		if( auth()->user()->sp_id != 0)
		{
	   		$sp_obj = new ServiceProvider();
	   		$service_provider = $sp_obj->getServiceProviderByID( auth()->user()->sp_id );
	   		if ( $service_provider['data'] != '')
	   		{
	   			$sp_info = json_decode($service_provider['data']);
				$section =  substr( $sp_info[0]->ServiceProviderName,0,50 ); //Shouldn't be longer than 50 Chars
	   		}
   		}
	   	//}

	   	return $section;
	}    

	public function getAllLogRecords()
	{
		try 
		{
			$response = $this->client->GetAllLogRecordsasJSON( );
			$logs = json_decode( $response->GetAllLogRecordsasJSONResult, true );
			foreach ($logs as $key => $log) 
			{
				$user = User::find($log['PersonID']);			
				
				if(isset($user->name))
				{
					$tempLog = array('PersonName'=>$user->name);					
					$logs[$key] = array_merge($log, $tempLog);		
				}	

			}
			return ['data' => $logs ];
		} 
		catch (Exception $e) 
		{
			return array( 'success' => 'error' , 'message' => 'Ups, something went wrong.' );		
		}
	}

    public function deleteTemplate( $te_id )
    {
        // Create call request        
        $info = [ 'RefNumber' => $te_id];

        try {
            $response = $this->client->DeleteTemplate($info);
            if($response->DeleteTemplateResult){
                return array( 'success' => 'success' , 'message' => 'NRE Template deleted.' );
            } else {
                return array( 'success' => 'error' , 'message' => 'Ups, something went wrong.' );
            }
        }
        catch (\Exception $e) {            
            return array( 'success' => 'error' , 'message' =>  $e->getMessage() );       
        }
    }
    /**
     * Sort the templates by section and group the templates.
     * @return Array Templates in select2 format
     */
    public function getAllTemplatesFormatedBySection(){
    	$templates = self::getAllTemplatesBySection()['data'];
    	$clean_templates = [];    	
    	foreach ($templates as $template) {    		    		    		
    		array_push($clean_templates, [ 
    										'id'   		=> $template['RefNo'], 
    										'text' 		=> $template['Name'],
    										'section'	=> $template['Section'],
    									]);    		
    	}
    	$templates = [];    	
    	foreach ($clean_templates as $key => $value) {	
    		$templates[ $value['section'] ][]		 = [
    									'id' 	=> $value['id'],
    									'text' 	=> $value['text'],
    								];
    	}
    	$output = [];
    	$generalTemplates = [];
    	foreach ($templates as $key => $value) {
    		$text = (strtoupper($key) == 'ALL' ? 'General Templates':$key .' Templates' ) ;
    		usort($value, function($a, $b){ return strcasecmp(strtoupper($a["text"]), strtoupper($b["text"])); });
    		if(strtoupper($key) != 'ALL')
    		{
    			$output[] = ['text' => $text, 'children' => $value];	
    		}    		    		
    		else
    		{
    			$generalTemplates = ['text' => $text, 'children' => $value];
    		}			
    	}
    	// insert General Templates at the end of the output array
    	$output[] = $generalTemplates;
    	return $output;

    }
    
    /**
     * Get All Send Emails by section
     * @return Array send emails filtered by section
     */
	public function getAllLogRecordBySection()
	{
		try 
		{
			$section = self::getSection();
			$response =		$this
							->client							
							->GetAllLogRecordsasJSON( );
			$logs = json_decode( $response->GetAllLogRecordsasJSONResult, true );
			$result = [];
			$is_admin = in_array( \App\Http\helpers::getRole(), ['Administrator']);
			foreach ($logs as $key => $log) 
			{
				$user = User::find($log['PersonID']);
				if(isset($user->name))
				{
					$tempLog = array('PersonName'=>$user->name);					
					$logs[$key] = array_merge($log, $tempLog);		
				}
				if($is_admin)
				{
					$result[] = $logs[$key];
				}	
				elseif(  $log['Section'] == $section )
				{										
					$result[] = $logs[$key];
				}


			}
			return ['data' => $result ];
		} 
		catch (Exception $e) 
		{
			return array( 'success' => 'error' , 'message' => 'Ups, something went wrong.' );		
		}	

	}    
    

}

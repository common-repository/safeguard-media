<?php if (!defined('ABSPATH')) exit;

function WPSM_get_client_id()
{
	$av_apikey = get_option('wpsm_av_apikey');

	$post = [
		'action' => 'getclient',
		'av_apikey' => $av_apikey,
		
	];
	$args = array(
		'body'        => $post,
		'timeout'     => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'cookies'     => array(),
	);
	$url='https://safeguard.media/asps/webservice.asp?action=getclient&av_apikey='.$av_apikey;
	$response = wp_remote_post( $url, $args );
	
	if( ! is_wp_error($response))
	{
		$parts = explode('~',$response['body']);

		if($parts[0] == 'success' && ! empty($parts[1])) {
			return $parts[1];
		}
	}

	return '';
}

function WPSM_get_server_files()
{
	$files     = [];
	
	$client_id = WPSM_get_client_id();

	$post = [
		'action' => 'getmedias',
		'clientid'=> $client_id,
	];
	
	$url='https://safeguard.media/asps/webservice.asp?action=getmedias&clientid=' . $client_id;
	$args = array(
		'body'        => $post,
		'timeout'     => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'cookies'     => array(),
	);
	
	$response = wp_remote_post( $url, $args );
	
	if( ! is_wp_error($response))
	{
		$parts = explode('~',$response['body']);

		if($parts[0] != 'success' || empty($parts[1])) {
			return $files;
		}
		
		$filelist = $parts[1];
		$filelist = trim($filelist, ', ');
		
		$files_data = explode(',', $filelist);

		if( ! empty($files_data) && count($files_data)>0)
		{
			foreach ($files_data as $file)
			{
				$fileparts = explode('@', $file);
				
				$filelink  = '';
				$filetitle = '';
				$date      = '';
				
				if(isset($fileparts[0]))
					$filelink=$fileparts[0];
				if(isset($fileparts[1]))
					$filetitle=$fileparts[1];
				if(isset($fileparts[2]))
					$date=$fileparts[2];

				if(empty($filelink) || empty($filetitle)) {
					continue;
				}

				$files[] = [
					'file'  => $filelink,
					'title' => $filetitle,
					'date'  => gmdate(get_option('date_format') . ' ' . get_option('time_format'), strtotime($date)),
				];
			}
		}
	}

	return $files;
}

function WPSM_register_media($safeguard_media_url, $client_id)
{
	$post = [
		'action'    => 'registermedias',
		'medialink' => $safeguard_media_url,
		'clientid'  => $client_id,
	];
	$args = array(
		'body'        => $post,
		'timeout'     => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'cookies'     => array(),
	);
	$url='https://safeguard.media/asps/webservice.asp?action=registermedias&medialink='.$safeguard_media_url.'&clientid='.$client_id;
	$response = wp_remote_post( $url, $args );

	if(  ! is_wp_error($response) && ! empty($response['body'])) {
		return explode('~',$response['body']);
	} else {
		return [];
	}
}
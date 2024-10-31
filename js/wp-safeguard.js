//  WP SafeGuard Media
//  Copyright (c) 2023 ArtistScope. All Rights Reserved.
//  safeguard.media
//
// Debugging outputs the generated html into a textbox instead of rendering


//===========================
//   DO NOT EDIT BELOW 
//===========================


// var m_bpDebugging = true;

// var m_bpWidth = "600";
// var m_bpHeight = "400";

function insertSafeGuardmedia(id, data)
{
	var OSName = "Unknown";
	if (window.navigator.userAgent.indexOf("Windows")!= -1) OSName="Windows";
	if (window.navigator.userAgent.indexOf("Mac")            != -1) OSName="Mac/iOS";
	if (window.navigator.userAgent.indexOf("Android")            != -1) OSName="Android";
	
	/* for iframe start */
	var watermarkmessage = '';
	if(data.av_watermark_onoff == "checked")
	{
		watermarkmessage = data.av_watermakdetailsenc;
	}

	if(data.m_allowwindows!=1 && OSName=='Windows' && data.browser == 'Artisbrowser' && parseFloat(data.version)>=parseFloat(data.av_Version_windows))
	{
		const para = document.createElement("p");
		para.innerHTML=errormessage;
		document.getElementById(id).appendChild(para);
		return false;
	}
	if(data.m_allowmac!=1 && OSName=='Mac/iOS' && data.browser == 'Artisbrowser' && parseFloat(data.version)>=parseFloat(data.av_Version_mac))
	{
		const para = document.createElement("p");
		para.innerHTML=errormessage;
		document.getElementById(id).appendChild(para);
		return false;
	}
	if(data.m_allowandroid!=1 && OSName=='Android' && data.browser == 'Artisbrowser' && parseFloat(data.version)>=parseFloat(data.av_Version_android))
	{
		const para = document.createElement("p");
		para.innerHTML=errormessage;
		document.getElementById(id).appendChild(para);
		return false;
	}
	if(data.m_allowios!=1 && OSName=='iOS' && data.browser == 'Artisbrowser' && parseFloat(data.version)>=parseFloat(data.av_Version_ios))
	{
		const para = document.createElement("p");
		para.innerHTML=errormessage;
		document.getElementById(id).appendChild(para);
		return false;
	}
	var src='https://safeguard.media/asps/framed-' + data.server_version + '.php?media='+encodeURIComponent(data.m_szClassName) +
		"&w=" + data.m_bpWidth +
		"&h=" + data.m_bpHeight +
		"&wm=" + watermarkmessage +
		"&r=" + data.av_allowremote +
		"&id=" + data.m_computerId;
	const iframe1 = document.createElement("iframe");
		iframe1.setAttribute('id', 'safeguard');
		iframe1.setAttribute('src', src);
		iframe1.setAttribute('width', data.m_bpWidth);
		iframe1.setAttribute('height', data.m_bpHeight);
		iframe1.setAttribute('scrolling', 'no');
		
	document.getElementById(id).appendChild(iframe1);

	if(data.m_bpDebugging)
	{
		const debugTag = document.createElement("p");
		debugTag.innerHTML = `Debug: ${src}`;

		document.getElementById(id).appendChild(debugTag);
	}
}
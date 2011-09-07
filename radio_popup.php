<?php
/**
 *   https://09source.kicks-ass.net:8443/svn/installer09/
 *   Licence Info: GPL
 *   Copyright (C) 2010 Installer09 v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn,kidvision.
 **/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(INCL_DIR.'user_functions.php');
require_once INCL_DIR.'html_functions.php';
dbconn(false);
loggedinorreturn();
error_reporting(E_ALL);

$lang = array_merge( load_language('global') );

$radio = array('host'=>'09source.kicks-ass.net',
			   'port'=>8000,'password'=>'richmond1');

$langs = array('CURRENTLISTENERS'=>'Current listeners: <b>%d</b>',
              'SERVERTITLE'=>'Server: <b>%s</b>',
              'SERVERURL'=>'Server url: <b>%s:'.$radio['port'].'</b>',
              'SONGTITLE'=>'Current song: <b>%s</b>',
              'BITRATE'=>'Bitrate: <b>%s kb</b>',
              'BITRATE'=>'Bitrate: <b>%s kb</b>',
              'PEAKLISTENERS'=>'Peak listeners: <b>%d</b>',
            );

  function radioinfo($radio) {
    global $langs, $INSTALLER09, $mc1, $CURUSER;    
    $xml = $html = $history = '';        
    if($hand = @fsockopen($radio['host'],$radio['port'],$errno,$errstr,30)) {
        fputs($hand, "GET /admin.cgi?pass=".$radio['password']."&mode=viewxml HTTP/1.1\nUser-Agent:Mozilla/5.0 ".
        "(Windows; U; Windows NT 6.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6\n\n");
        while(!feof($hand))
                $xml .= fgets($hand,1024);
        preg_match_all('/\<(SERVERTITLE|SERVERURL|SONGTITLE|STREAMSTATUS|BITRATE|CURRENTLISTENERS|PEAKLISTENERS)\>(.*?)<\/\\1\>/iU',$xml,$tempdata,PREG_SET_ORDER);
        foreach($tempdata as $t2)
                $data[$t2[1]] = isset($langs[$t2[1]]) ? sprintf($langs[$t2[1]],$t2[2]) : $t2[2];
        unset($tempdata);
        preg_match_all('/\<SONG>(.*?)<\/SONG\>/',$xml,$temph);
        unset($temph[0][0],$temph[1]);
        $history = array(); 
        foreach($temph[0] as $temph2) {
                preg_match_all('/\<(TITLE|PLAYEDAT)>(.*?)<\/\\1\>/i',$temph2,$temph3,PREG_PATTERN_ORDER);
                $history[] = '<b>&nbsp;'.$temph3[2][1].'</b> <sub>('.get_date(time(), 'DATE' ,$temph3[2][0]).')</sub>';
        }
        
        if($data['STREAMSTATUS'] == 0)
                return 'Sorry '.$CURUSER['username'].'... : Server '.$radio['host'].' is online but there is no stream';
        else {
                unset($data['STREAMSTATUS']);
                $md5_current_song = md5($data['SONGTITLE']);
                $current_song = $mc1->get('current_radio_song');
                if($current_song === false || $current_song != $md5_current_song) {
                   autoshout(str_replace(array('<','>'),array('[',']'),$data['SONGTITLE'].' playing on '.strtolower($data['SERVERTITLE']).' - '.strtolower($data['SERVERURL'])));
                   $mc1->cache_value('current_radio_song',$md5_current_song,0);

                }
                $html = '<fieldset>
                <legend>'.$INSTALLER09['site_name'].' radio</legend><ul>';
                foreach($data as $d)
                        $html .= '<li><b>'.$d.'</b></li>';
                        $html .= '<li>Playlist history: '.(count($history) > 0 ? join(', ',$history) : 'No playlist history');
                $html .= '</li></ul></fieldset>';
                return $html;
        }
        } 
    else 
    $html .='<fieldset><legend>'.$INSTALLER09['site_name'].' radio</legend>
    <font size="3" color="red"><img src="'.$INSTALLER09['pic_base_url'].'off1.gif" alt="Off" title="Off" border="0" /><br />
    <b>Sorry '.$CURUSER['username'].' Radio is currently Offline</b></font></fieldset><br />';
    return $html;
    }
    
    $HTMLOUT = '';
    $HTMLOUT = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
    <meta name='generator' content='TBDev.net' />
	  <meta name='MSSmartTagsPreventParsing' content='TRUE' />
		<title>{$INSTALLER09['site_name']} Radio</title>
    <link rel='stylesheet' href='./templates/1/1.css' type='text/css' />
    </head>
    <body>";
  
    $HTMLOUT .="<script type='text/javascript'>
    /*<![CDATA[*/
    function roll_over(img_name, img_src)
    {
    document[img_name].src=img_src;
    }
    /*]]>*/
    </script>";
    $HTMLOUT .= "<h2>{$INSTALLER09['site_name']} Site Radio</h2>".
    "<div  align=\"center\">".
    "<a href=\"http://".$radio['host'].":".$radio['port']."/listen.pls\" onmouseover=\"roll_over('winamp', 'pic/winamp_over.png')\" onmouseout=\"roll_over('winamp', 'pic/winamp.png')\" style=\"border:hidden;\" ><img src=\"pic/winamp.png\" name=\"winamp\" alt=\"Click here to listen with Winamp\" title=\"Click here to listen with Winamp\" style=\"border:hidden;\" /></a>".
    "<a href=\"http://".$radio['host'].":".$radio['port']."/listen.asx\" onmouseover=\"roll_over('wmp', 'pic/wmp_over.png')\" onmouseout=\"roll_over('wmp', 'pic/wmp.png')\" style=\"border:hidden;\" ><img src=\"pic/wmp.png\" name=\"wmp\" alt=\"Click here to listen with Windows Media Player\" title=\"Click here to listen with Windows Media Player\" style=\"border:hidden;\" /></a>".
    "</div>".radioinfo($radio);
    
    $HTMLOUT .= "<div align='center'><a class='altlink' href='javascript: window.close()'><b>[ Close window ]</b></a></div></body></html>";
echo $HTMLOUT;
?>
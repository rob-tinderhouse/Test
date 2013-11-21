<?php 
ini_set("display_errors", 1);

// Something before
// Something

require("core.php");
require("attach_mailer_class.php");
require("JSON.php");
$json = new Services_JSON();

function do_post_request($url, $params){
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_POST, count(explode("&", $params)));
	curl_setopt($c, CURLOPT_POSTFIELDS, $params);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	$contents = curl_exec($c);
	curl_close($c); 
  return $contents;	
}

function checkConfig($company_epic){
	global $REPORTS_DB;
	$query = $REPORTS_DB->query("SELECT access_level FROM visible_to_client WHERE epic = '".$company_epic."' LIMIT 1");
	if(count($query) == 0) stopWith("ERROR: No client access set");
	else {
		$access = $query[0]['access_level'];
		return ($access == 1)? true : false;
	}
}

function getCompanyName($epic){
	global $WEB_DB;
	$query = $WEB_DB->query("SELECT c.cat_name FROM eir_categories AS c, eir_category_field_data AS d WHERE c.cat_id = d.cat_id AND d.field_id_2 = '".$epic."' LIMIT 1");
	return $query[0]['cat_name'];
}

function getEmailTemplate($type){
	if($type == "header") return '
<html>
    <head>
        <title>Edison Investment Research</title>
    </head>
    <body bgcolor="#e5e5e5">
        <table width="550" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" align="center" style="FONT-FAMILY: arial,sans-serif">
            <style type="text/css">

				body { font-family: arial, sans-serif; font-size: 12px; margin: 0; color: #191919; } 
				* body a { color: #191919;}
				* body a:hover {text-decoration: none;}
				* img {border: 0;}
				* .topNote { font-size: 12px; color: #2e2e2e; padding: 10px 0 8px 0; }
			</style>
            <tbody>
                <tr>
                    <td bgcolor="#e5e5e5" align="center" colspan="2" class="topnote" style="PADDING-BOTTOM: 5px; PADDING-LEFT: 0pt; PADDING-RIGHT: 0pt; FONT-SIZE: 12px; PADDING-TOP: 0pt"><small>This email contains graphics. If you do not see them, <a href="%HTMLVersion%"><span style="COLOR: #191919">view it in your browser</span></a>.</small></td>
                </tr>
				<tr>
                	<td colspan="2">
                    	<table border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td colspan="3" bgcolor="#68b74f">&nbsp;</td>
							</tr>
							<tr>
								<td align="right" rowspan="2"><img width="200" height="62" src="http://www.edisoninvestmentresearch.co.uk/images/emassets/eir2013.gif" alt="Edison Investment Research" /></td>
								<td>&nbsp;</td>
								<td width="10" align="right">&nbsp;</td>
							</tr>
							<tr>                                                       
								<td width="340" align="right" valign="top" style="font-size: 24px; font-weight: bold; color: #191919;"></td>
								<td width="10" align="right">&nbsp;</td>
							</tr>
						</table>
                    </td>
                </tr>
                <!-- /header  --><!-- body -->
                                    <td valign="top" colspan="2" style="PADDING-BOTTOM: 10px; PADDING-LEFT: 13px; PADDING-RIGHT: 13px; FONT-FAMILY: arial,sans-serif; COLOR: #191919; FONT-SIZE: 12px; PADDING-TOP: 10px">

<h3>InvestorTrack<sup>&#174;</sup> Report</h3>';
	
	else if($type == "footer") return '
	<br /><br />
    								</td>
                <!-- /body --><!-- footer -->
                <tr>
                    <td bgcolor="#68b74f" colspan="2" style="PADDING-BOTTOM: 10px; PADDING-LEFT: 13px; PADDING-RIGHT: 13px; FONT-FAMILY: arial,sans-serif; COLOR: #ffffff; FONT-SIZE: 12px; PADDING-TOP: 10px">
                    <p><strong>Edison Investment Research</strong><br />Email: <a href= "mailto:enquiries@edisoninvestmentresearch.com" style="text-decoration:none"><span style="COLOR: #ffffff; TEXT-DECORATION: none">enquiries@edisoninvestmentresearch.com</span></a><br />Web: <a href="http://www.edisoninvestmentresearch.com" style="text-decoration:none"><span style="COLOR: #ffffff; TEXT-DECORATION: none">www.edisoninvestmentresearch.com</span></a></p>
                    <table cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td width="130" valign="top" style="FONT-FAMILY: arial,sans-serif; COLOR: #ffffff; FONT-SIZE: 12px">
                                <p>London +44 (0)20 3077 5700 <br />
                                280 High Holborn<br />
                                London<br />
                                WC1V 7EE<br />
                                UK</p>
                                </td>
                                <td width="130" valign="top" style="FONT-FAMILY: arial,sans-serif; COLOR: #ffffff; FONT-SIZE: 12px">New York +1 646 653 7026 <br />
                                245 Park Avenue <br />
                                39th Floor <br />
                                New York, NY 10167 <br />
                                US</td>
                                <td width="130" valign="top" style="FONT-FAMILY: arial,sans-serif; COLOR: #ffffff; FONT-SIZE: 12px">
                                <p>Sydney +61 (0)2 9258 1162<br />
                                Level 33, Australia Square<br />
                                264 George St, Sydney<br />
                                NSW 2000<br />
                                Australia</p>
                                </td>
                                <td width="130" valign="top" style="FONT-FAMILY: arial,sans-serif; COLOR: #ffffff; FONT-SIZE: 12px">
                                <p>Frankfurt: +49 (0)69 78 8076 960<br />
                                Schumannstrasse 34b<br />
                                60325 Frankfurt<br />
                                Germany</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p>Registered in England, no. 4794244. Copyright 2013 all rights reserved Edison Investment Research Limited. Authorised and Regulated by the Financial Services Authority.<br /></p>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#e5e5e5" colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td bgcolor="#e5e5e5" colspan="2" style="FONT-FAMILY: arial,sans-serif; FONT-SIZE: 10px">
                    <p>This email was sent to %Member:Email%. If you no longer wish to receive Edison monthly email, <a href="mailto:Unsubscribe@edisoninvestmentresearch.com?subject=Unsubscribe%20Monthly%20Book">click here</a><br /></p>
                    <p>To unsubscribe from all news updates, reports and research from Edison Investment Research, <a href="mailto:unsubscribe@edisoninvestmentresearch.com?subject=Unsubscribe%20me%20from%20all%20Edison%20emails">click here</a>.<br /></p>
                    <p>Powered by <a href="http://www.tinderhouse.com"><span style="COLOR: #191919">Tinderhouse</span></a></p>
                    </td>
                </tr>
                <!-- /footer  -->
            </tbody>
        </table>
    </body>
</html>
	';
}

$client          = "true";
$epic            = $_POST['epic'];
$recipients      = $_POST['recipients'];

$company_name    = getCompanyName($epic);
$date_format     = "d M Y";
$from            = date($date_format, strtotime("-12 months"));
$to              = date($date_format);
$query_manager   = "http://www.edisoninvestmentresearch.co.uk/analytics-engine/query-manager";
$process_manager = "http://www.edisoninvestmentresearch.co.uk/analytics-engine/process-manager";

/* READERS IN PERIOD JOB PROCESSING */
$type          = "readers_in_period";
$params        = "client=".$client."&epic=".$epic."&from=".$from."&to=".$to."&type=".$type."&refresh=true";
$job_details   = $json->decode(do_post_request($query_manager, $params), true);
$job_id        = $job_details->job_id;
$job_stages    = $job_details->stages;

echo "Job created: ".$type." (".$job_id.")<BR>";
for($i = 0; $i < $job_stages; $i++){
	echo "Processing stage ".($i + 1)." / ".$job_stages."<BR>";
	$process_params = "job_id=".$job_id."&stage=".$i;
	$process = do_post_request($process_manager, $process_params);
}

/* ACTIVE READERS JOB PROCESSING */
/*
$type          = "active_readers";
$params        = "client=".$client."&epic=".$epic."&from=".$from."&to=".$to."&type=".$type."&refresh=true";

$job_details   = $json->decode(do_post_request($query_manager, $params));
$job_id        = $job_details->job_id;
$job_stages    = $job_details->stages;

echo "<BR>Job created: ".$type." (".$job_id.")<BR>";

for($i = 0; $i < $job_stages; $i++){
	echo "Processing stage ".($i + 1)." / ".$job_stages."<BR>";
	$process_params = "job_id=".$job_id."&stage=".$i;
	$process = do_post_request($process_manager, $process_params);
}
*/
$full_report = checkConfig($epic);
$body = getEmailTemplate("header");

if($full_report){
	$body .= "Please find attached the latest edition of ".$company_name."'s InvestorTrack<sup>&#174;</sup> report.<BR><BR>"."\n\n";
	$body .= "These reports contain valuable data, which enable you to evaluate the interest of potential investors and shareholders in your company over a defined time frame.<BR><BR>"."\n\n";
	$body .= "We encourage you to review the reports and share them with your advisers to strengthen your investor relations strategy and the ability to target new investors. You can also view these reports <strong>online</strong> with the username and password you received when you signed up for the service..<BR><BR>"."\n\n";
	$body .= "<strong>Edison's Investor Access</strong> team can provide you with a more detailed analysis and understanding of the data presented in this report. The team can also facilitate face-to-face meetings with potential and existing investors.<BR><BR>"."\n\n";
	$body .= "Please contact us if you would like to discuss the attached report in more detail, or would like to know more about our Investor Access service.<BR><BR>";
	$body .= "Yours sincerely, <BR><BR>";
	$body .= "<strong>Damir Hadziosmanovic</strong><BR>";
	$body .= "Account Director<BR>";
	$body .= "Edison Investor Access<BR>";
	$body .= "T: +44 (0)20 3077 5700<BR>";
	$body .= "E: itrack@edisongroup.com<BR>";
	//$body .= "Follow us on Twitter<BR>";
} else {
	$body .= "Please find attached the latest edition of ".$company_name."'s summary InvestorTrack<sup>&#174;</sup> report.<BR>"."\n";
	$body .= "Please note this is only a <strong>summary report</strong>, providing you with a high-level statistical breakdown of the performance and readership of your research.<BR><BR>"."\n\n";
	$body .= "The full InvestorTrack<sup>&#174;</sup> report provides access to the company names and locations of investors reading your research reports and more detail about how they accessed them, for example whether the Edison report was downloaded from our website or from Bloomberg. This provides you and your advisers with a useful tool to strengthen your investor relations strategy, together with the ability to target new investors.<BR><BR>"."\n\n";
	$body .= "The summary report contains an overview of the number of notes read, the channels used to access your research and the readers' location and profile. This enables you to evaluate the interest of investors in your company over a defined time frame. You can also view InvestorTrack<sup>&#174;</sup> , using the username and password you received when you signed up for our research service.<BR><BR>"."\n\n";
	//$body .= "As mentioned above, this is a summary report. <BR><BR>"."\n\n";
	$body .= "Please contact us if you would like to upgrade to the full InvestorTrack<sup>&#174;</sup> report, or to find out more about our Investor Access service.<BR><BR>"."\n\n";
	$body .= "Yours sincerely, <BR><BR>"."\n\n";
	$body .= "<strong>Damir Hadziosmanovic</strong><BR>";
	$body .= "Account Director<BR>";
	$body .= "Edison Investor Access<BR>";
	$body .= "T: +44 (0)20 3077 5700<BR>";
	$body .= "E: itrack@edisongroup.com<BR>";
	//$body .= "Follow us on Twitter<BR>";
}

/*
if($full_report){
	$body .= 'Please find attached the latest InvestorTrack<sup>&#174;</sup> report on '.$company_name.'.<BR><BR>'."\n\n";
	$body .= "InvestorTrack<sup>&#174;</sup> is the platform custom built by Edison to track the readership of our research reports.<BR><BR>"."\n\n";
	$body .= "The data contained in these reports is enormously valuable and we encourage you to use it internally and to share it with your advisors in order to enhance your IR strategy and ability to target new investors.<BR><BR>"."\n\n";
	$body .= "Edison's Investor Access team can drill much deeper into this data and combine investor interest across companies and sectors. Please contact the team directly if you would like to discuss the additional services they can provide: eia@edisonaccess.com or telephone +44 (20) 3077 5700.";
	
} else {
	$body .= 'Please find attached the latest InvestorTrack<sup>&#174;</sup> summary report on '.$company_name.'. Please note this only a summary report. In order to access the full report you will need to subscribe to the full InvestorTrack<sup>&#174;</sup> service.<BR><BR>'."\n\n";
	$body .= "InvestorTrack<sup>&#174;</sup> is the platform custom built by Edison to track the readership of our research reports.<BR><BR>"."\n\n";
	$body .= "InvestorTrack<sup>&#174;</sup> provides the company names and locations of investors reading Edison's research reports and information on how they accessed it (for example, via email, Edison's website and online platforms such as Bloomberg).  Client companies' research is available to all investors worldwide, and Edison has a dedicated team focused on the continuous development of our distribution and tracking capabilities.<BR><BR>"."\n\n";
	$body .= "Please contact Edison's corporate sales team if you would like to upgrade to the full InvestorTrack<sup>&#174;</sup> service: sales@edisoninvestmentresearch.co.uk or telephone +44 (20) 3077 5710.";
}
*/
/* 
$body .= "Please find attached your first InvestorTrack&#174; Report on ".$company_name.", which will be followed by further reports every two months hereafter. These reports will replace any existing readership reports you may have received previously.<BR><BR>"."\n\n";

$body .= "InvestorTrack&#174; is the new platform custom built by Edison to track the readership of our research reports. It represents a substantial investment by Edison and is a significant improvement on our previous readership reports:</p>"."\n\n";

$body .= "<ul><li>More sophisticated tracking tools</li>
              <li>More sources of our research tracked (e.g. more online platforms, peer tagging, Edison/Client websites)</li>
							<li>More detailed investor information: who, where, how report was accessed</li>
							<li>New layout and data fields</li>
							<li>Online access available from January 2012 onwards</li>
				  </ul>"."\n";
					
$body .= "<p>The data contained in these reports is enormously valuable and we encourage you to use it internally and to share it with your advisors in order to enhance your IR strategy and ability to target new investors.<BR><BR>"."\n\n";

$body .= "Edison's Investor Access team can drill much deeper into this data and combine investor interest across companies and sectors. Please contact the team directly if you would like to discuss the additional services they can provide: eia@edisonaccess.com or telephone +44 (20) 3077 5702.";
*/

$body .= getEmailTemplate("footer");


/* SEND OUT EMAIL */

echo "<BR>Preparing email";
$name       = "InvestorTrack";
$from_email = "investortrack@edisoninvestmentresearch.co.uk";
//$to_email   = "joe.angus@tinderhouse.com";
$cc         = "dhadziosmanovic@edisonaccess.com";
$bcc        = "";
$subject    = $company_name." - Edison InvestorTrack Report";

$email      = new attach_mailer($name, $from_email, "joe.angus@tinderhouse.com", $cc, $bcc, $subject, $body);

echo "<BR>Preparing PDF";
echo "<BR>http://www.edisoninvestmentresearch.co.uk/assets/php/analytics/pdf.php?epic=".$epic."&hash=2af9156f34ebb2b621a24ed3c1a27f5a5c88b6dd&from=".urlencode($from)."&to=".urlencode($to)."&toconfiguration=yes&lang=Last 12 months";
$email->create_attachment_part("http://www.edisoninvestmentresearch.co.uk/assets/php/analytics/pdf.php?epic=".$epic."&hash=2af9156f34ebb2b621a24ed3c1a27f5a5c88b6dd&from=".urlencode($from)."&to=".urlencode($to)."&toconfiguration=yes&lang=Last 12 months"); 
echo "<BR>PDF created";

//$email->process_mail();

$email->send_mail_to("SArora@edisoninvestmentresearch.co.uk");	
$email->send_mail_to("jabetti@edisoninvestmentresearch.co.uk");	
$email->send_mail_to("rob.smith@tinderhouse.com");	
//$email->send_mail_to("nick.tatt@tinderhouse.com");	
//$email->send_mail_to("nick.tatt@tinderhouse.com");
//$email->send_mail_to("glyn.murray@tinderhouse.com");

$recipients = explode(",", $recipients);
foreach($recipients as $r){
	echo "<BR>Sending email to ".$r;
  $email->send_mail_to($r);	
}


echo "<BR>Email(s) sent";

?>


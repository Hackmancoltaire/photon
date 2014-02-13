#!/usr/bin/perl -w

# It is important for your httpd.conf to have ExecCGI added
# for the directory /Library/WebServer/Documents . Otherwise
# it this will not be executable in the PHOTON directory.

use CGI qw(:cgi);
CGI::private_tempfiles(0);

# Define Variables

	# Get Unique ID Passed from PHP
	my $UUID = (split(/[&=]/,$ENV{QUERY_STRING}))[1];

	# Define Directory Paths (Must be Absolute Paths)
	my $upload_dir = "/tmp/PHOTON-uploads/";
	my $tmp_dir = "/tmp/PHOTON-pendingUploads/";
	my $session_dir = $tmp_dir.$UUID;

	# Define Variable for Upload Size
	my $upload_size_file = $session_dir.'/upload_size';
	my $upload_size=0;
	my $max_upload = 400; # In Megabytes

	$max_upload = ($max_upload * 1024 * 1024);
	$CGI::POST_MAX = $max_upload;
	
	# Debuging Output
	my $debug = 1;

################################################################################ 
## Auto Flush
################################################################################

umask(0);
$|++;    

################################################################################ 
## Start Processing Upload
################################################################################

print "Content-type: text/html\n\n";
if ($debug) { print "AJAX Perl Upload Script<br>\n"; }

################################################################################ 
## Create Upload Directory if it does not exist
################################################################################

if ($UUID eq "") {

}
elsif ($ENV{'CONTENT_LENGTH'} > $max_upload || $UUID eq "") {
	print "<script type='text/javascript'>parent.cancelUpload('Max Upload Size Exceeded. " . $ENV{'CONTENT_LENGTH'} . ":$max_upload');</script>";
}
else {

	print "Checking for Uploads Directory Should be $tmp_dir-> ";
	
	if (-d $upload_dir) {
		print "<b style='color:green;'>Found</b><br>"; 
		chmod 0777, $upload_dir;
	}
	else {
		print "Creating Upload Directory : ";
		
		if (mkdir($upload_dir, 0777)) { print "<b style='color:green;'>Success</b><br>"; }
		else { print "<b style='color:red;'>Failure</b> Could not make directory. Should be: " . $upload_dir . "<br>";}
	}

	if (-d $upload_dir . $UUID . "/") {
		print "<b style='color:green;'>Found</b><br>"; 
		chmod 0777, $upload_dir . $UUID . "/";
	}
	else {
		print "Creating Upload Directory : ";
		
		if (mkdir($upload_dir . $UUID . "/", 0777)) { print "<b style='color:green;'>Success</b><br>"; }
		else { print "<b style='color:red;'>Failure</b> Could not make directory. Should be: " . $upload_dir . $UUID . "/" . "<br>";}
	}
	
	################################################################################ 
	## Create Temporary Directory
	################################################################################
	
	print "Check for Temp Directory -> ";
	
	if (-d $tmp_dir) {
		print "<b style='color:green;'>Found</b><br>";
		chmod 0777, $tmp_dir;
	}
	else {
		print "Creating Temp Directory: "; 
		if (mkdir($tmp_dir, 0777)) { print "<b style='color:green;'>Success</b><br>"; }
		else { print "<font color='red'>Failure. Could not make directory. Should be: " . $tmp_dir . "</font><br>"; }
	}
	
	################################################################################ 
	## Create Session Temporary Directory
	################################################################################
	
	print "Check for Session Temp Directory. Should be $session_dir -> ";
	
	if (-d $session_dir) {
		print "<b style='color:green;'>Found</b><br>";
		chmod 0777, $session_dir;
	}
	else {
		print "Creating Directory: "; 
		if (mkdir($session_dir, 0777)) { print "<b style='color:green;'>Success</b><br>"; }
		else { print "<font color='red'>Failure. Could not make directory. Should be: " . $session_dir . "</font><br>"; }
	}
	
	################################################################################ 
	## Check Upload Size and continue
	################################################################################
	
		# Create upload_size File
	
		print "Creating Upload Size file -> ";
		open FLENGTH, ">$upload_size_file";
			$upload_size = $ENV{'CONTENT_LENGTH'};
			print FLENGTH $upload_size;
		close FLENGTH;
		chmod 0777, $upload_size_file;
	
		if (-e $upload_size_file) { print "<b style='color:green;'>Success</b><br>"; }
		else { print "<b style='color:red;'>Failure. Could not write size file to: " . $upload_size_file . "</b><br>";}
		
		# Relocate Temporary File Directory
	
		print "Setting Private Temp Directory -> ";
	
		if ($TempFile::TMPDIRECTORY) { 
			print "<b style='color:green;'>Success TempFile - (" . $TempFile::TMPDIRECTORY . ") Set to $session_dir</b><br>";
			$TempFile::TMPDIRECTORY = $session_dir;
		}
		elsif ($CGITempFile::TMPDIRECTORY) {
			print "<b style='color:green;'>Success CGITempFile - (" . $CGITempFile::TMPDIRECTORY . ") Set to $session_dir</b><br>";
			$CGITempFile::TMPDIRECTORY = $session_dir; 
		}
		else { print "<b style='color:red;'>Failure</b><br>"; }
		
		sleep(2);
	
		# Process Uploaded File
	
		if (-d $session_dir) { 
			my $query = new CGI;
			my $file_name = $query->param("file");
			$file_name =~ s/.*[\/\\](.*)/$1/;
			my $upload_file_path = $upload_dir . $UUID . "/" .$file_name;
			my $upload_filehandle = $query->upload("file");
			my $tmp_filename = $query->tmpFileName($upload_filehandle);

			close($upload_filehandle);
			
			print "Moving File ($tmp_filename) to Upload Directory ($upload_file_path) -> ";
	
			if (rename($tmp_filename, $upload_file_path)) { print "<b style='color:green;'>Success</b><br>"; }
			else { print "<font color='red'>Failure</font><br>"; }
	
			print "Removing upload_size file -> ";
	
			if (unlink($upload_size_file)) { print "<b style='color:green;'>Success</b><br>"; }
			else { print "<font color='red'>Failure</font><br>";}
		}
	
	################################################################################ 
	## Remove Session Temporary File
	################################################################################
	
	print "Removing Session Temporary Directory -> ";
	
	if (rmdir($session_dir)) { print "<b style='color:green;'>Success</b><br>"; }
	else { print "<font color='red'>Failure</font><br>";}
	
#	for $key ( param() ) {
#		$input{$key} = param($key);
#	}
#	
#	for $key ( keys %input ) {
#		print $key, ' = ', $input{$key}, "<br>\n";
#	}

}
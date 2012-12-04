<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
	<% base_tag %>
	$ExtendedMetatags
</head>
<body id="body{$ClassName}">
<div id="Wrapper">
	<div id="Container">
		<div id="Layout" class="typography">
<% if EmailAFriendForm %>
			<h2>$Title</h2>
			<p>You can send the link of this page to your friends by filling the form below :</p>
			$EmailAFriendForm

<% end_if %>
<% if EmailAFriendThankYouContent %>
			<div id="EmailAFriendThankYouContent">$EmailAFriendThankYouContent</div>
<% end_if %>
		</div>
	</div>
</div>
<% include Analytics %>
</body>
</html>
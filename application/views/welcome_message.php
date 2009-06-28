<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>TinyCIMM demo</title>
	<style type="text/css">
		body { background-color: #fff; margin: 40px; font-family: Lucida Grande, Verdana, Sans-serif; font-size: 14px; color: #4F5155; } 
		a { color: #003399; background-color: transparent; font-weight: normal; } 
		h1 { color: #444; background-color: transparent; border-bottom: 1px solid #D0D0D0; font-size: 16px; font-weight: bold; margin: 24px 0 2px 0; padding: 5px 0 6px 0; }
		code { font-family: Monaco, Verdana, Sans-serif; font-size: 12px; background-color: #f9f9f9; border: 1px solid #D0D0D0; color: #002166; display: block; margin: 14px 0 14px 0; padding: 12px 10px 12px 10px; } 
	</style>
	<?= $this->load->view('common/wysiwyg');?>
</head>
<body>

<h1>TinyCIMM Image demo</h1>

<p>The demo is using TinyMCE Version: 3.2.3 and CodeIgniter Version 1.7.1</p>

<p>View the source of this page to see how the plugin is integrated into tinymce as a file browser callback.</p>

<p>View an example of the context menu integration by right-clicking on an image and selecting 'Resize'.</p>

<div>
<textarea id="demo_textarea" rows="25" cols="80">&nbsp;</textarea>
</div>

<?=$this->load->view('common/footer.php');?>

</body>
</html>

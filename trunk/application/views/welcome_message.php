<html>
<head>
<title>TinyCIMM demo</title>

<style type="text/css">

body {
 background-color: #fff;
 margin: 40px;
 font-family: Lucida Grande, Verdana, Sans-serif;
 font-size: 14px;
 color: #4F5155;
}

a {
 color: #003399;
 background-color: transparent;
 font-weight: normal;
}

h1 {
 color: #444;
 background-color: transparent;
 border-bottom: 1px solid #D0D0D0;
 font-size: 16px;
 font-weight: bold;
 margin: 24px 0 2px 0;
 padding: 5px 0 6px 0;
}

code {
 font-family: Monaco, Verdana, Sans-serif;
 font-size: 12px;
 background-color: #f9f9f9;
 border: 1px solid #D0D0D0;
 color: #002166;
 display: block;
 margin: 14px 0 14px 0;
 padding: 12px 10px 12px 10px;
}

</style>
<?= $this->load->view('common/wysiwyg');?>
</head>
<body>

<h1>TinyCIMM demo</h1>

<p>View the source of this page to see how the plugin is integrated into tinymce.</p>

<p>The demo is using TinyMCE Version: 3.2.3 and CodeIgniter Version 1.7.1</p>

<textarea id="demo_textarea">Welcome to the TinyCIMM demo page. This is a default TinyMCE editor. Click on the image icon in the editor toolbar to view a demonstration of the 
image manager. </textarea>

</body>
</html>

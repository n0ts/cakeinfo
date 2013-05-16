<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html><head>
<style type="text/css">
body {background-color: #ffffff; color: #000000;}
body, td, th, h1, h2 {font-family: sans-serif;}
pre {margin: 0px; font-family: monospace;}
a:link {color: #000099; text-decoration: none; background-color: #ffffff;}
a:hover {text-decoration: underline;}
table {border-collapse: collapse;}
.center {text-align: center;}
.center table { margin-left: auto; margin-right: auto; text-align: left;}
.center th { text-align: center !important; }
td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
h1 {font-size: 150%;}
h2 {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #ccccff; font-weight: bold; color: #000000;}
.h {background-color: #003D4C; font-weight: bold; color: #ffffff;}
.v {background-color: #cccccc; color: #000000;}
.vr {background-color: #cccccc; text-align: right; color: #000000;}
img {float: right; border: 0px;}
hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
</style>
<title>cakeinfo()</title>
</head>

<body><div class="center">
<table border="0" cellpadding="3" width="600">
<tr class="h">
<td>
<a href="http://cakephp.org/"><img border="0" src="/img/cake.power.gif" alt="CakePHP Logo" /></a>
<h1 class="p">CakePHP Version <?php echo Sanitize::html($info->version) ?></h1>
</td></tr>
</table><br />
<hr />
<?php foreach ($info->values as $section => $blocks): ?>
<h1><?php echo Sanitize::html($section) ?></h1>
  <?php foreach ($blocks as $name => $values): ?>
    <?php if ($name): ?>
  <h2><?php echo Sanitize::html(Inflector::camelize($name)) ?></h2>
    <?php endif; ?>
  <table border="0" cellpadding="3" width="600">
    <tr class="h"><th>Name</th><th>Value</th></tr>
    <?php foreach ($values as $k => $v): ?>
      <tr>
        <td class="e"><?php echo Sanitize::html($k) ?></td>
        <td class="v">
          <?php if (is_array($v)): ?>
            <?php foreach ($v as $kk => $vv): ?>
              <?php if (is_array($vv)): ?>
                <?php foreach ($vv as $kkk => $vvv): ?>
                  <?php Sanitize::html($kkk); echo $info->toString($kkk); ?>
                  =>
                  <?php Sanitize::html($vvv); echo $info->toString($vvv); ?>
                  <br />
                <?php endforeach; ?>
              <?php else: ?>
              <?php Sanitize::html($kk); echo $info->toString($kk); ?>
              =>
              <?php Sanitize::html($vv); echo $info->toString($vv); ?>
              <br />
              <?php endif; ?>
            <?php endforeach; ?>
          <?php elseif (is_object($v)): ?>
          ..
          <?php else: ?>
            <?php Sanitize::html($v); echo $info->toString($v); ?>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php endforeach; ?>
<br />
<?php endforeach; ?>
<hr />
</body>
</html>


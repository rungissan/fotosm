<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title><?php echo $title ?> | Intersog <?php echo 'User Guide'; ?></title>

<?php foreach ($styles as $style => $media) echo HTML::style($style, array('media' => $media), NULL, TRUE), "\n" ?>

<?php foreach ($scripts as $script) echo HTML::script($script, NULL, NULL, TRUE), "\n" ?>

</head>
<body> 
    <div id="kodoc-header">
            <div class="container">
                    <a href="/guide/" id="kodoc-logo">
                            <?=HTML::image(Route::get('media')->uri(array('file' => 'logo.png')))?>
                    </a>
                    <div id="kodoc-menu">
                            <ul>
                                    <li class="guide first">
                                            <a href="https://chrome.google.com/webstore/detail/postman-rest-client/fdmmgilgnpjigdojojpjoooidkmcomcm">API Browser</a>
                                    </li>
                            </ul>
                    </div>
            </div>
    </div>

    <div id="kodoc-content">
            <div class="wrapper">
                    <div class="container">
                            <div class="span-22 prefix-1 suffix-1">
                                    <ul id="kodoc-breadcrumb">
                                            <?php foreach ($breadcrumb as $link => $title): ?>
                                                    <?php if (is_string($link)): ?>
                                                    <li><?php echo HTML::anchor($link, $title, NULL, NULL, TRUE) ?></li>
                                                    <?php else: ?>
                                                    <li class="last"><?php echo $title ?></li>
                                                    <?php endif ?>
                                            <?php endforeach ?>
                                    </ul>
                            </div>
                            <div class="span-6 prefix-1">
                                    <div id="kodoc-topics">
                                            <?php echo $menu ?>
                                    </div>
                            </div>
                            <div id="kodoc-body" class="span-16 suffix-1 last">
                                    <?php echo $content ?>

                                    <?php if ($show_comments): ?>
                                            <!-- comment here -->
                                    <?php endif ?>
                            </div>
                    </div>
            </div>
    </div>

    <div id="kodoc-footer">
            <div class="container">
                    <div class="span-12">
                    <?php /* if (isset($copyright)): ?>
                            <p><?php echo $copyright ?></p>
                    <?php else: ?>
                            &nbsp;
                    <?php endif */?>
                        <p>&copy; 2008–2014 <a href="http://intersog.com">Intersog</a></p>
                    </div>
                    <div class="span-12 last right">
                        <p>Powered by Kohana v<?php echo Kohana::VERSION ?></p>
                    </div>
            </div>
    </div> 
<?php if (Kohana::$environment === Kohana::PRODUCTION): ?>
    <!-- comment here -->
<?php endif ?>
</body>
</html>

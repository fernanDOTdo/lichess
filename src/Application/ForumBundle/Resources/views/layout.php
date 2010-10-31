<?php $view['stylesheets']->add('bundles/forum/css/forum.css'); ?>
<?php $view['javascripts']->add('bundles/lichess/js/jquery.min.js'); ?>
<?php $view['javascripts']->add('bundles/lichess/js/ctrl.js'); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Language" content="en">
        <title><?php $view['slots']->output('title', 'Forum') ?> | Lichess free online Chess game<?php $view['slots']->output('title_suffix', '') ?></title>
        <meta content="<?php $view['slots']->output('description', 'Lichess form') ?>" name="description">
        <meta content="Chess, Chess game, play Chess, online Chess, free Chess, quick Chess, anonymous Chess, opensource, PHP, JavaScript, artificial intelligence" name="keywords">
        <meta content="index, follow" name="robots">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
        <?php echo $view['slots']->get('feed_link') ?>
        <?php echo $view['stylesheets'] ?>
    </head>
    <body>
        <div class="content">
            <div class="header">
                <div><a class="site_title" href="<?php echo $view['router']->generate('lichess_homepage') ?>">Lichess</a></div>
                <?php $view['slots']->output('baseline', 'Don\'t register. Play Chess.') ?>
            </div>
            <div id="lichess_forum">
                <?php $view['slots']->output('_content') ?>
            </div>
        </div>
        <div class="footer_wrap">
            <ul class="lichess_social"></ul>
            <div class="footer">
                <div class="right">
                    <?php echo $view['translator']->_('Contact') ?>: <span class="js_email"></span><br />
                    <a href="<?php echo $view['router']->generate('lichess_about') ?>" target="_blank">Learn more about Lichess</a>
                </div>
                <div class="nb_connected_players" data-url="<?php echo $view['router']->generate('lichess_nb_players') ?>">
                    <?php echo $view['translator']->_('%nb% connected players', array('%nb%' => $view['lichess']->getNbConnectedPlayers())) ?>
                </div>
                Get <a href="http://github.com/ornicar/lichess" target="_blank" title="See what's inside, fork and contribute">source code</a> or give <a href="<?php echo $view['router']->generate('forum_category_show', array('slug' => 'lichess-feedback')) ?>" title="Having a suggestion, feature request or bug report? Let me know">feedback</a> or <a href="<?php echo $view['router']->generate('lichess_translate') ?>">help translate Lichess</a><br />
                <?php echo $view['translator']->_('Open Source software built with %php%, %symfony% and %jqueryui%', array('%php%' => 'PHP 5.3', '%symfony%' => '<a href="http://symfony-reloaded.org" target="_blank">Symfony2</a>', '%jqueryui%' => '<a href="http://jqueryui.com/" target="_blank">jQuery UI</a>')) ?><br />
            </div>
        </div>
        <div title="Come on, make my server suffer :)" class="lichess_server">
            <?php $loadAverage = sys_getloadavg() ?>
            <?php echo $view['translator']->_('Server load') ?>: <span class="value"><?php echo round(25*$loadAverage[1]) ?></span>%
        </div>
        <div id="top_menu">
            <a class="goto_play goto_nav" title="<?php echo $view['translator']->_('Play a new game') ?>" href="<?php echo $view['router']->generate('lichess_homepage') ?>">Play</a>
            <a class="goto_gamelist goto_nav" title="<?php echo $view['translator']->_('See the games being played in real time') ?>" href="<?php echo $view['router']->generate('lichess_games') ?>">Games</a>
        </div>
        <?php echo $view['javascripts'] ?>
    </body>
</html>

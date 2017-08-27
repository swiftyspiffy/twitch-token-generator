<!DOCTYPE html>
<html lang="en">
<head>
    <title>Twitch Token Generator by swiftyspiffy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.js"></script>
    <script src="script.js"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/ico" sizes="48x48" href="../favicon-48x48.ico">
</head>
<div class="col-md-2"></div>
<div class="container col-md-8">
    <br>
    <h5>
        <a href="https://twitchtokengenerator.com">Take me back to TwitchTokenGenerator.com</a>
    </h5>
    <br>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title text-center">Twitch Token Generator Anonymized Statistics</h3>
        </div>
        <div class="panel-body">
            <span>Below are statics gathered through usage of the tool. The only data tracked is the popularity of scopes as well as total tool consumption. No personal info is associated with this data.</span>

        <div class="row">
            <div class="col-md-12">
                <canvas id="scope-chart" width="800" height="450"></canvas>
            </div>
        </div>
        </div>
    </div>
    <div class="row text-center">
        <span><i>Website Source: <a href="https://github.com/swiftyspiffy/twitch-token-generator" target="_blank">Repo</a><br>This tool was created and is maintained by swiftyspiffy. <br><a href="https://twitch.tv/swiftyspiffy" target="_blank">Twitch</a> | <a href="https://twitter.com/swiftyspiffy" target="_blank">Twitter</a> | <a href="https://github.com/swiftyspiffy" target="_blank">GitHub</a><i></span>
    </div>
    <br><br>
</div>
<script>
    /* --- GA START --- */
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-91354985-1', 'auto');
    ga('send', 'pageview');
    /* --- GA END --- */


</script>
</html>



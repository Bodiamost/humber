<?php
/**
 * Created by PhpStorm.
 * User: bodia
 * Date: 2017-04-25
 * Time: 3:36 AM
 */
?>
<!DOCTYPE html>
<html>
<head lang="en">
    <title><?php echo $item['name']?> | Restaurant Locator</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="js/jquery.1.11.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <meta charset="utf-8" />
</head>
<body>
<header class="row">
    <h1 class="pull-left col-lg-offset-2 col-md-offset-2 col-sm-offset-1 col-xs-offset-1 logo">Restaurant Locator</h1>
<form class="pull-right" method="post" action="index.php">
    <button class="loginBtn loginBtn--exit" type="submit" name="logout">Logout</button>
</form>
    <p class="welcome">Hello, <?php echo $userInfo['name'];?>! </p>
</header>
<div class="page-wrapper">
    <div class="centered box row">
        <?php
            echo '<h2>'.$item['name'].'</h2><div class="item col-lg-8 col-md-8 col-sm-8">';
            if ($item['photoref']!='')
                echo "<img src='https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=".$item['photoref']."&key=".$google_API_KEY."' alt='Image of ".$item['name']."'>";
            else
                echo "<span style='color: green;'> No image available</span><br/>";
            echo '</div><div class="col-lg-4 col-md-4 col-sm-4" >';

            echo $item['address']!=''?"<div>Address: ".$item['address'].'</div>':"";
            echo $item['phone']!=''?"<div>Phone: ".$item['phone'].'</div>':"";

            if($item['weekly_open']!='')
            {
                echo "<h3>Open hours:</h3>";
                foreach ($item['weekly_open'] as $day){
                    echo $day.'<br/>';
                }
            }
            else
                echo "<span style='color: green;'> No open time data available</span><br/>";
            echo "</div>";
            echo "<div>";
            ?>
        <div class="content-row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#google">Google
                            <?php
                                if($item['rating']!='')
                                {
                                    $int=intval($item['rating']*2);
                                    if($int==0) echo "<span>Not rated yet</span>";
                                    $i=1;
                                    for($i=1; $i<$int;$i+=2)
                                        echo "<i class=\"fa fa-star fa-2x\" aria-hidden=\"true\"></i>";
                                    if($i==$int) echo "<i class=\"fa fa-star-half-o fa-2x\" aria-hidden=\"true\"></i>";
                                }
                            ?></a></li>
                    <li class="pull-right"><a data-toggle="tab" href="#foursquare">Foursquare
                            <?php
                                if(isset($item['foursquareInfo']->rating))
                                {
                                    $int=intval($item['foursquareInfo']->rating);
                                    if($int==0) echo "<span>Not rated yet</span>";
                                    $i=1;
                                    for($i=1; $i<$int;$i+=2)
                                        echo "<i class=\"fa fa-star fa-2x\" aria-hidden=\"true\"></i>";
                                    if($i==$int) echo "<i class=\"fa fa-star-half-o fa-2x\" aria-hidden=\"true\"></i>";
                                }
                            ?></a></li>
                </ul>

                <div class="tab-content">
                    <div id="google" class="tab-pane fade in active">
                        <?php
                            echo '<br/><br/>';
                            if(count($item['reviews'])!=0)
                            {
                                echo '<h3>Google reviews:</h3>';
                            }
                            else
                                echo "No google reviews yet!";
                            echo "<div style='text-align: left;'>";
                            foreach ($item['reviews'] as $review){
                                if($review['text']!='')
                                {
                                    echo '<div>';
                                    echo '<h4><i>'.$review['author_name'].':</i></h4>';
                                    echo '<p>'.$review['text'].'</p>';
                                    echo '</div>';
                                }
                            }
                            echo "</div>";
                        ?>
                    </div>
                    <div id="foursquare" class="tab-pane fade">
                        <?php

                            if(isset($item['foursquareInfo']))
                            {
                                echo '<div>';
                                echo '<h3>Foursqure reviews:</h3>';
                                echo "<div style='text-align: left;'>";
                                foreach ($item['foursquareInfo']->tips->groups[0]->items as $review){
                                    echo '<div>';
                                    echo '<h4><i>'.$review->user->firstName;
                                    if(isset($review->user->lastName)) echo " ".$review->user->lastName.':</i></h4>';
                                    else echo ':</i></h4>';
                                    echo '<p>'.$review->text.'</p>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            }else echo "Not found on foursquare!";
                        ?>
                    </div>
                    <div id="location" class="tab-pane fade">
                        <div id="map" style="height: 400px;"></div>
                    </div>
                </div>
                <div></div>
            </div>
        </div>
    </div>
</div>
<div>
<a href="app.php">Back to list</a>
</div>
</body>
</html>
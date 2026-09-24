<?php
    include 'handler.php'; 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon_io/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon_io/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon_io/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
        <link rel="stylesheet" type="text/css" href="styles/fr.css" /> 
       <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
       <script src="fr.js"></script>
       
        <title>Finger Rocket Game</title>
        
    </head>
    <body>
    <h1>Finger Rocket Game</h1>

    <?php
    
       /* $testHandler = new combatHandler();
        print "Points: " . $testHandler->player->getPoints() . "<br/>";
        $testHandler->player->addToArmory(7);
        print "Points now: " . $testHandler->player->getPoints() . "<br/>";*/
    ?>

    <div id="login">
        <div id="loginForm">
            <form  id="fr_login" method="post">
                <div>
                    <label for="username">Email</label>
                    <input type="text"  id="username" name="username" />
                </div>
                <div>
                    <label for="pass">Password</label>
                    <input type="text"  id="pass" name="pass"  />
                </div>
                    <button type="submit" id="submitLogin">Log In</button> 
                    <p class="account_switch">Don't have an account? <a href="#">Sign Up</a>
            </form>
        </div><!-- End login div -->
        <div id="gameOptions" class="hidden">
            <form id="game_options" name="game_options">
                <input type="hidden" id="options_playerId" name="options_playerId"/>
                
                <fieldset id="options_fieldset">
                    <legend>Choose a game</legend>
                </fieldset>
                 <button type="submit" id="submitOption">Play Game</button> 
            </form>
        </div><!-- End game options div -->
        <div id="signupForm" class="hidden">
            <form id="fr_signup" method="post">
                <div>
                    <label for="signup_username">Email</label>
                    <input type="text"  id="signup_username" name="signup_username" placeholder="name@name.com" aria-describedby="username_error" />
                    <div id="username_error" class="hidden">Please provide a valid email address</div>
                    <div id="username_not_unique" class="hidden">That email is already being used</div>
                </div>
                <div>
                    <label for="signup_pass">Password</label>
                    <input type="text"  id="signup_pass" name="signup_pass" aria-describedby="pass_error"/>
                    <div id="pass_error" class="hidden">Password must be at least 8 characters</div>
                </div>
                    <button type="submit" id="submitSignupLogin">Sign Up</button> 
                    <p class="account_switch">Already have an account? <a href="#">Log In</a>
            </form>
        </div><!-- End signup form div -->
        <div>
            <p id="sendId" class="hidden">Email a friend and invite them to play. Give them this ID: <span id="uid"></span></p>
            <div id="linkId" class="hidden">
                <p>Received a friend's ID? </p>
                <form id="link_friend_form" method="post">
                    <input type="hidden" id="link_friend_form_playerId" name="playerId"/>
                    <label for="friendId">Friend's ID</label>
                    <input type="text" id="friendId" name="friendId" />
                    <button type="submit">Link Friend's ID</button>

                </form>
            </div>
        </div><!-- End friend id div -->
        <div id="newGame" class="hidden">
            <form id="new_game_form" method="post">
                <imput type="hidden" id="new_game_playerId" name="new_game_playerId"/>
                <button type="submit">Start New Game</button>
            </form>
        </div><!-- End start new game div -->
        <div id="workshop">
            <form name="workshop_form">
                <button id="workshopToggle">Toggle Workshop</button>
                <div id="inner">
                    <h2>Your Workshop</h2>
                    <div id="rocket_parts">
                        <h3>Rocket Parts</h3>
                    </div>
                    <div id="cladding_parts">
                        <h3>Cladding Parts</h3>
                    </div>
                    <div id="blueprints">
                        <h3>Blueprints</h3>
                    </div>
                    <div id="crafted_items">
                        <h3>Crafted Items</h3>
                    </div>
                </div>
            </form>
        </div><!-- End workshop div -->

    </div>
   
     <div id="error"></div>
<div class="container">
      <div class="column player player hidden">
            <div class="fortress" id = "player">
                
                    <h2 id="player_heading"> <?php /* print $combatHandler->player->name; */ ?></h2>
                
                    <!-- <img src="images/mountain_castle.jpg"/> -->
                    <div class = "points">
                        <label for="player_points">Points</label>
                        <input readonly="true" value="<?php /* print $combatHandler->player->getPoints(); */ ?>" id="player_points" name="player_points" />
                    </div>
                    <div class = "flak">
                        <label for="player_flak">Flak</label>
                        <input readonly="true" value="<?php /* print $combatHandler->player->getFlak(); */ ?>" id="player_flak" name="player_flak" />
                    </div>

                    <div class = "cladding">
                        
                        <label for="player_cladding">Cladding material</label>
                        
                        <input readonly="true" id="player_cladding" value = "<?php /* print $combatHandler->player->convertCladdingToString($combatHandler->player->getDamageResistance()); */ ?>"/>
                    </div>

                
                <div class="emplacement">
                <form action="index.php" method="post" id="player_attackForm" name="player_attackForm">
                    
                    <label for="player_rockets">Rockets</label>
                    <select id = "player_rockets" name="player_rockets">
                        <option>Fire a rocket</option>
                        <?php
                            /* $combatHandler->player->displayRockets(); */
                        ?>
                    </select>
                    <input  type="hidden" id="player_turn" name="player_turn" />
                    <input type="hidden" id="attack_hId" name="attack_hId"/>
                    <input type="hidden" id="attack_pId" name="attack_pId"/>
                    <button type="submit" <?php /* if(isset($_POST['player_turn'])){print " disabled ";} */ ?> class="attack" id="player_attack">Attack!</button>
                </form>
                </div>
                <div id="player_error"></div>
                <h3 id="player_shopName"><?php /* print $combatHandler->player->name; */ ?> Store</h3>
                
                <div class="gunshop">
                <form action="index.php" method="post" id="player_gunForm">
                    <input type="hidden" id="gunshopPlayerSlot" name="playerSlot"/>
                    <input type="hidden" id="gunshop_hId" name="hId"/>
                    <div class="rocket">
                        <label for = "player_gunShop">Choose a rocket to buy</label>
                        <select id = "player_gunShop" name="player_gunShop">
                            <option>Choose a rocket</option>
                            <option value="0">Finger Rocket (cost: 1 pt)</option>
                            <option value="1">Dart (cost: 3 pt)</option>
                            <option value="2">Fletchette (cost: 5 pt)</option>
                            <option value="3">Bolt (cost: 7 pt)</option>
                            <option value="8">Cluster Rocket (5 Fingers)</option>
                            <option value="4">ICYMI  (cost: 9 pt)</option>
                            <option value="5">ICBM (cost: 11 pt)</option>
                            <option value="6">TCB (cost: 13 pt)</option>
                            <option value="7">Can of Whoop Ass (cost: 15 pt)</option>
                        </select>
                    </div>
                    <div>
                        <label for="player_quan">How many? (default is one)</label><input type="text" id="player_quan" name="player_quan" value="1" size="2"/>
                        <button type="submit" <?php /* if(isset($_POST['player_turn'])){print " disabled ";} */ ?> id="player_buy">Buy rocket!</button>
                    </div>
                </form>
                </div>

                <div class="cladding">
                <form action="index.php" method="post" id="player_claddingForm">
                    <input type="hidden" id="claddingPlayerSlot" name="playerSlot"/>
                    <input type="hidden" id="cladding_hId" name="hId"/>
                    <label for = "player_claddingShop">Choose cladding to buy</label>
                    <?php /* writeCladdingShop($combatHandler->player); */ ?>
                    <select id = "player_claddingShop" name="player_claddingShop">
                    <option>Choose cladding</option>
                            <option value="1">Vanadium (cost: 20pt)</option>
                            <option value="2">Iron (cost: 30pt)</option>
                            <option value="3">Titanium (cost: 40pt)</option>
                            <option value="4">Chromium (cost: 50pt)</option>
                            <option value="5">Steel (cost: 60pt)</option>
                            <option value="6">Tungsten (cost: 70pt)</option>
                            <option value="7">Mithril (cost: 80pt)</option>
                            <option value="8">Admantium (cost: 90pt)</option>
                    </select>
                    <button type="submit" <?php /* if(isset($_POST['player_turn'])){print " disabled ";} */ ?> id="player_buy_cladding">Buy cladding!</button>
                </form>
                </div>

                <div class="flak">
                <form action="index.php" method="post" id="player_flakForm">
                    <input type="hidden" id="flakPlayerSlot" name="playerSlot"/>
                    <input type="hidden" id="flak_hId" name="hId"/>
                    <label for = "player_flakShop">Choose flak to buy</label>
                    <select id = "player_flakShop" name="player_flakShop">
                    <option>Choose flak (5 pts each)</option>
                            <option value="5">1</option>
                            <option value="10">2</option>
                            <option value="15">3</option>
                            <option value="20">4</option>
                            <option value="25">5</option>
                    </select>
                    <button type="submit" <?php /* if(isset($_POST['player_turn'])){print " disabled ";} */ ?> id="player_buy_flak">Buy flak!</button>
                </form>
                </div>


                <div class="nameChange">
                    <form action="index.php" method="post" id="player_nameForm">
                        <input type="hidden" id="nameChangePlayerSlot" name="playerSlot"/>
                        <input type="hidden" id="nameChange_hId" name="hId"/>
                        <label for="player_nameChange">Change fortress name</label>
                        <input type="text" id="player_nameChange" name="player_nameChange" />
                        <button type="submit" id="player_change_name">Change name!</button>
                    </form>
                </div>

            </div>
        </div>
  


        <div class="column monitor hidden">
            <h2 id="opponent_heading"><?php /* print $combatHandler->opponent->name; */ ?></h2>
            <!--<img src="images/amber_castle.jpg" /> -->
                <div class = "points">
                    <label for="opponent_points">Points</label>
                    <input readonly="true" id="opponent_points" name="opponent_points" value="<?php /* print $combatHandler->opponent->points */ ?>" />
                </div>
                <div class = "flak">
                    <label for="opponent_flak">Flak</label>
                    <input readonly="true" value="<?php /* print $combatHandler->player->getFlak(); */ ?>" id="opponent_flak" name="opponent_flak" />
                </div>
                <div class = "cladding">
                    <label for="opponent_cladding">Cladding material</label>
                    <input readonly="true" id="opponent_cladding" value = "<?php /* print $combatHandler->opponent->convertCladdingToString($combatHandler->opponent->getDamageResistance()); */ ?>"/>
                </div>
            </div>
    
    <div class="column player gamelog hidden">
        <h2>Game Log</h2>
        <div id="gameLog" aria-live="polite">
        <?php
            /* $combatHandler->displayGameLog(); */
        ?>
        </div>
       
        
    
    </div>
    <?php
        unset($_POST['player_turn']);
        unset($_POST['opponent_turn']);

    ?>
    <!-- Hidden input to trigger initialization -->
    <form  id="fr_fort" method="post">
        <input type="hidden" id="initPlayerId"  name="playerId" value="000001"/>
        <input type="hidden" name="hId" value="1"/>
            
        <input type="hidden"  id="fr_test" name="fr_test"/>
           <button type="submit">submit</button>
    </form>
    </body>
    </html>
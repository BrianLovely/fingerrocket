// Initializes game UI behavior using shared closure state and the page DOM; takes no arguments and returns nothing.
$(document).ready(function() {
    var attackInFlight = false;
    var turnActionInFlight = false;
    var gameStateRevision = 0;
    var currentGameStatus = 'active';
    var currentTurnPlayerId = null;

    // Shows a generic AJAX error in the page; uses no arguments and returns nothing.
    $( document ).on( "ajaxError", function() {
        $( ".error" ).text( "Triggered ajaxError handler." );
      } );

    // Switches the login form to signup on mouseup; uses the event context and returns nothing.
    $("#login a").on("mouseup", function(){
        $("#loginForm").removeClass("authenticated").addClass("hidden");
        $("#signupForm").removeClass("hidden").addClass("authenticated");
      });

    // Switches the signup form to login on mouseup; uses the event context and returns nothing.
    $("#signupForm a").on("mouseup", function(){
        $("#signupForm").removeClass("authenticated").addClass("hidden");
        $("#loginForm").removeClass("hidden").addClass("authenticated");
      });

            // Toggles the workshop after preventing the default click using `$event`; returns nothing.
            $( "#workshopToggle" ).on( "click", function(event) {
                event.preventDefault();
        $("#inner").toggleClass("hidden");
    } );

    // Copies `$pId` into the player-ID form fields; returns nothing.
    function setPlayerId(pId){
        $("#initPlayerId").val(pId);
        $("#nameChangePlayerId").val(pId);
        $("#flakPlayerId").val(pId);
        $("#claddingPlayerId").val(pId);
        $("#gunshopPlayerId").val(pId);
        $("#attackPlayerId").val(pId);
        
      }

    // Reads session IDs and turn/in-flight state to enable or disable action controls; returns nothing.
    function refreshActionControls(){
        var playerId = sessionStorage.getItem('playerId') || sessionStorage.getItem('pId');
        var canAct = currentGameStatus === 'active'
            && currentTurnPlayerId === playerId
            && !attackInFlight
            && !turnActionInFlight;
        $('#player_attack, #player_buy, #player_buy_cladding, #player_buy_flak').prop('disabled', !canAct);
        $('.blueprint').prop('disabled', !canAct);
      }

    // Serializes `$form` and appends session player/handler IDs; returns the URL-encoded request string.
    function turnActionData(form){
        var fields = $(form).serializeArray();
        fields.push({name: 'action_playerId', value: sessionStorage.getItem('playerId') || sessionStorage.getItem('pId') || ''});
        fields.push({name: 'action_handlerId', value: sessionStorage.getItem('handlerId') || sessionStorage.getItem('hId') || ''});
        return $.param(fields);
      }

    // Sends `$data` when the current player may act, uses `$errorMessage` and optional `$isSuccessful`, and updates the UI; returns nothing.
    function submitTurnAction(data, errorMessage, isSuccessful){
        var playerId = sessionStorage.getItem('playerId') || sessionStorage.getItem('pId');
        if(turnActionInFlight || attackInFlight || currentGameStatus !== 'active' || currentTurnPlayerId !== playerId){
            return;
        }

        turnActionInFlight = true;
        gameStateRevision++;
        refreshActionControls();
        var actionFailed = false;
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            dataType: 'json',
            data: data,
            // Validates `$response` with `$isSuccessful` and updates player/opponent state; returns nothing.
            success: function(response){
                if(response.error){
                    actionFailed = true;
                    $('#player_error').text(response.error);
                    return;
                }
                if(isSuccessful && !isSuccessful(response)){
                    actionFailed = true;
                    $('#player_error').text(response.craft && response.craft.error || errorMessage);
                    return;
                }
                var json = JSON.stringify(response);
                if(updatePlayer(json, true)){
                    updateOpponent(json);
                } else {
                    actionFailed = true;
                    $('#player_error').text(errorMessage);
                }
            },
            // Displays `$errorMessage` and logs `$status`/`$error` for failed `$xhr`; returns nothing.
            error: function(xhr, status, error){
                actionFailed = true;
                $('#player_error').text(errorMessage);
                console.error('Turn action failed:', status, error);
            },
            // Clears the in-flight state and resumes polling after the request; takes no arguments and returns nothing.
            complete: function(){
                turnActionInFlight = false;
                refreshActionControls();
                if(currentGameStatus === 'active'){
                    startGamePolling();
                    if(actionFailed && !gamePollInFlight){
                        refreshGameState();
                    }
                }
            }
        });
      }

        // Parses `$data` into player/game UI and updates logs according to `$replaceLog`; returns whether required state is present.
        function updatePlayer(data, replaceLog){
        /* Display fortress one values */
        console.log(data);
        var playerData = JSON.parse(data);
            if (!playerData['player'] || !playerData['f1']) {
                $('#player_error').text(playerData['error'] || 'The game state is incomplete. Refresh and choose the game again.');
                return false;
            }
            $('.player, .monitor, .gamelog').removeClass('hidden');
            var gameStatus = playerData['gameStatus'] || 'active';
            currentGameStatus = gameStatus;
            currentTurnPlayerId = playerData['playerUp'];
            var isPlayerTurn = currentTurnPlayerId === playerData['player']['id'];
        if (!isPlayerTurn) {
            $('#player_error').text('Wait for the other player to take their turn.');
        } else {
            $('#player_error').text('');
        }
        sessionStorage.setItem('playerId', playerData['player']['id']);
        sessionStorage.setItem('handlerId', playerData['handlerId']);
        $('#attack_pId').val(playerData['player']['id']);
        $('#attack_hId').val(playerData['handlerId']);
        var playerArmory = playerData['f1']['armory'];
        var log = playerData['log'];
        var items = JSON.parse(playerData['player']['items'] || '[]');
        console.log("items: " + items[1]);
        updateWorkshop(items);
        updateLog(log, replaceLog);
        refreshActionControls();
       $('#link_friend_form_playerId').val(playerData['player']['id']);
        $('#player_points').val(playerData['f1']['points']);
        $('#targetScoreStatus').text('Target score: ' + playerData['targetScore']).removeClass('hidden');
        $('#player_heading').text(playerData['f1']['name']);
        $('#player_shopName').text(playerData['f1']['name'] + ' Shop');
        $('#player_flak').val(playerData['f1']['flak']);
        $('#player_cladding').val(playerData['f1']['cladding']);
        var selectedRocket = $('#player_rockets').val();
        $("#player_rockets").empty();
        $("#player_rockets").append("<option>Fire a rocket</option>");
        // Renders each armory `$value` using its `$key`; returns nothing.
        $.each( playerArmory, function( key, value ) {
            $("#player_rockets").append("<option value='" + value["id"] + "'>" + value["name"] + "</option>");
        }); 
        // Tests each option's `this.value` against `$selectedRocket`; returns a boolean for filtering.
        if($("#player_rockets option").filter(function(){ return this.value === selectedRocket; }).length){
            $("#player_rockets").val(selectedRocket);
        }

        if (gameStatus === 'won') {
            var winnerName = playerData['winnerId'] === playerData['player']['id']
                ? playerData['f1']['name']
                : playerData['f2']['name'];
            var currentHighScore = Math.max(Number(playerData['f1']['points']), Number(playerData['f2']['points']));
            var nextMinimum = currentHighScore + 1;
            var nextTarget = Math.max(Number(playerData['targetScore']) + 1000, nextMinimum);
            $('#winnerAnnouncement').text(winnerName + ' wins by reaching ' + playerData['targetScore'] + ' points.');
            $('#outcomeHandlerId').val(playerData['handlerId']);
            $('#next_target_score').attr('min', nextMinimum).val(nextTarget);
            $('#nextTargetForm').toggleClass('hidden', currentHighScore >= 1000000000);
            $('#gameOutcome').removeClass('hidden');
            $('#gameEnded').addClass('hidden');
        } else if (gameStatus === 'ended') {
            $('#gameOutcome, #targetScoreStatus').addClass('hidden');
            $('#gameEnded').removeClass('hidden');
            $('.player, .monitor, .gamelog').addClass('hidden');
            if (gamePoll) {
                clearInterval(gamePoll);
                gamePoll = null;
            }
        } else {
            $('#gameOutcome, #gameEnded').addClass('hidden');
        }
        if (gameStatus !== 'active' && gamePoll) {
            clearInterval(gamePoll);
            gamePoll = null;
        }
        return true;
    }

    // Renders rocket debris, cladding materials, blueprints, and crafted items from `$items`; returns nothing.
    function updateWorkshop(items){
        $("#rocket_parts, #cladding_parts, #raw_materials, #blueprints, #crafted_items").find("p, button.blueprint").remove();
        if (!Array.isArray(items)) {
            return;
        }
        if(items[0].length > 0){
            // Renders each rocket-debris `$value` at `$key`; returns nothing.
            $.each( items[0], function( key, value ) {
                $("#rocket_parts").append("<p>" + (value.name || value) + "</p>");
            });
        }
        if(items[1].length > 0){
            // Renders each cladding-debris `$value` at `$key`, or its raw-material label; returns nothing.
            $.each( items[1], function( key, value ) {
                if (Number(value.typeId) === 35) {
                    var material = (value.material || '').toLowerCase();
                    var label = 'Piece of raw' + (material ? ' ' + material : '');
                    $('#raw_materials').append($('<p>').text(label));
                    return;
                }
                $("#cladding_parts").append("<p>" + (value.name || value) + "</p>");
            });
        }
        if(items[2].length > 0){
            // Renders each blueprint `$value` at `$key` as a crafting button; returns nothing.
            $.each( items[2], function( key, value ) {
                var blueprintId = value.id || '';
                var blueprintName = value.module || value.name || value;
                $("#blueprints").append("<button type='button' class='blueprint' data-blueprint-id='" + blueprintId + "'>Craft " + blueprintName + "</button>");
            });
        }
        if(items[3] && items[3].length > 0){
            // Renders each crafted-item `$value` at `$key`; returns nothing.
            $.each(items[3], function(key, value){
                $("#crafted_items").append("<p>" + (value.name || value) + "</p>");
            });
        }
    }

    // Parses `$data` and updates the opponent fortress fields when points are present; returns nothing.
    function updateOpponent(data){
        /* Display fortress two values */
        var opponentData = JSON.parse(data);
        if(opponentData['f2']['points'] != undefined){
            $('#opponent_points').val(opponentData['f2']['points']);
            $('#opponent_heading').text(opponentData['f2']['name']);
            $('#opponent_flak').val(opponentData['f2']['flak']);
            $('#opponent_cladding').val(opponentData['f2']['cladding']);
            
        }
    }


    var knownLogLength = 0;
    var logInitialized = false;

    // Prepends normalized text for each game-log value in `$entries`; returns nothing.
    function appendLogEntries(entries){
        // Normalizes and renders each log `$value` at `$key`; returns nothing.
        $.each(entries, function(key, value){
            var logText = String(value).replace(/<br\s*\/?>/gi, ' ').replace(/\s+/g, ' ').trim();
            $('#gameLog').prepend($('<p>').text(logText));
        });
    }

    // Updates rendered log entries from `$log`, replacing or appending according to `$replaceLog`; returns nothing.
    function updateLog(log, replaceLog){
        if (!Array.isArray(log)) {
            return;
        }
        if (replaceLog && !logInitialized) {
            $('#gameLog').empty();
            appendLogEntries(log.slice().reverse());
            knownLogLength = log.length;
            logInitialized = true;
            return;
        }
        if (replaceLog) {
            if (log.length < knownLogLength) {
                $('#gameLog').empty();
                appendLogEntries(log.slice().reverse());
                knownLogLength = log.length;
                return;
            }
            var newCount = log.length - knownLogLength;
            if(newCount > 0){
                appendLogEntries(log.slice(0, newCount).reverse());
            }
            knownLogLength = log.length;
            return;
        }
        if(log.length > 0){
            appendLogEntries(log.slice().reverse());
            knownLogLength += log.length;
            logInitialized = true;
        }
    }

    // Placeholder accepting `$data`; currently performs no work and returns nothing.
    function appendOption($data){
    }

    var gamePoll;
    var gamePollInFlight = false;

    // Requests the current game state for session player/handler IDs unless a request/action is already active; returns nothing.
    function refreshGameState(){
        var playerId = sessionStorage.getItem('playerId');
        var handlerId = sessionStorage.getItem('handlerId') || sessionStorage.getItem('hId');
        if (!playerId || !handlerId || gamePollInFlight || attackInFlight || turnActionInFlight || currentGameStatus !== 'active') {
            return;
        }
        var requestRevision = gameStateRevision;
        gamePollInFlight = true;
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: {refresh_playerId: playerId, refresh_handlerId: handlerId},
            // Applies response `$data` only when its revision is current; returns nothing.
            success: function(data){
                if (attackInFlight || requestRevision !== gameStateRevision) {
                    return;
                }
                var jsonData = JSON.parse(data);
                if (!jsonData['error'] && updatePlayer(data, true)) {
                    updateOpponent(data);
                } else if (jsonData['error']) {
                    $('#player_error').text(jsonData['error']);
                }
            },
            // Shows a retry message for the current poll revision; takes no arguments and returns nothing.
            error: function(){
                if (requestRevision === gameStateRevision) {
                    $('#player_error').text('Unable to refresh game state. Retrying.');
                }
            },
            // Clears the polling in-flight flag; takes no arguments and returns nothing.
            complete: function(){
                gamePollInFlight = false;
            }
        });
    }

    // Replaces the game polling interval with a one-second refresh; returns nothing.
    function startGamePolling(){
        if (gamePoll) {
            clearInterval(gamePoll);
        }
        gamePoll = setInterval(refreshGameState, 1000);
    }

    // Reads the clicked blueprint ID and submits a crafting turn action; uses the event context and returns nothing.
    $(document).on('click', '.blueprint', function(){
        var blueprintId = $(this).data('blueprint-id');
        submitTurnAction({
            craft_blueprint_id: blueprintId,
            action_playerId: sessionStorage.getItem('playerId') || sessionStorage.getItem('pId'),
            action_handlerId: sessionStorage.getItem('handlerId') || sessionStorage.getItem('hId')
        }, 'Blueprint crafting failed.',
        // Returns whether `$response` reports successful crafting.
        function(response){
            return response.craft && response.craft.success;
        });
    });

    // Renders login/game-choice UI from `$jsonData`, optionally clearing the selected game; returns nothing.
    function renderLoggedInView(jsonData, clearGameState){
        if (!jsonData || !jsonData.playerExists) {
            return;
        }
        if (clearGameState) {
            sessionStorage.removeItem('handlerId');
            sessionStorage.removeItem('hId');
        }

        $('#login_error').text('').addClass('hidden');
        $('#loginForm, #signupForm').addClass('hidden');
        $('.player, .monitor, .gamelog').addClass('hidden');
        $('#gameOutcome, #gameEnded, #targetScoreStatus').addClass('hidden');
        $('#linkId').removeClass('hidden');
        sessionStorage.setItem('pId', jsonData.playerId);
        sessionStorage.setItem('playerId', jsonData.playerId);
        $('#options_playerId, #link_friend_form_playerId').val(jsonData.playerId);

        if (jsonData.handlerExists) {
            $('#gameOptions').removeClass('hidden');
            $('.account_switch').addClass('hidden');

            var fortresses = jsonData.fortresses;
            if (!Array.isArray(fortresses)) {
                fortresses = fortresses && typeof fortresses === 'object' ? [fortresses] : [];
            }

            var $fieldset = $('#options_fieldset');
            $fieldset.children('.option').remove();
            // Builds one game-choice control from `$fortress` at `$index`; returns nothing.
            $.each(fortresses, function(index, fortress) {
                var optionId = 'game-option-' + index;
                var description = fortress.playerFortressName + ' with ' + fortress.playerFortressPoints +
                    ' points, versus ' + fortress.opponentFortressName + ' with ' + fortress.opponentFortressPoints +
                    ' points (target: ' + fortress.targetScore + ')';
                var $option = $('<div>', {class: 'option'});
                var $radio = $('<input>', {
                    type: 'radio',
                    name: 'options_handlerId',
                    id: optionId,
                    value: fortress.handlerId
                });
                var $label = $('<label>').attr('for', optionId).text(description);
                $fieldset.append($option.append($radio, $label));
            });
        } else {
            $('#gameOptions').addClass('hidden');
            $('#new_game_playerId').val(jsonData.playerId);
            $('#newGame').removeClass('hidden');
        }
    }

    /* Log In */
    // Prevents native submission and sends login form data; uses `$e` and returns nothing.
    $('#fr_login').submit(function(e) {
        e.preventDefault();
        $('#login_error').text('').addClass('hidden');

        $.ajax({
            type: 'POST',
            url: 'handler.php',
            dataType: 'json',
            data: $(this).serialize(),
            // Renders the logged-in state from `$jsonData` or displays a login error; returns nothing.
            success: function(jsonData) {
                if (!jsonData || !jsonData.playerExists) {
                    $('#login_error').text('Invalid email or password.').removeClass('hidden');
                    return;
                }
                renderLoggedInView(jsonData, true);
            },
            // Logs `$status`/`$error` for failed `$xhr` and displays a login error; returns nothing.
            error: function(xhr, status, error) {
                console.error('Login request failed:', status, error);
                $('#login_error').text('Unable to complete login. Please try again.').removeClass('hidden');
            }
        });
    });
    

    // Prevents native signup submission and sends the form; uses `$e` and returns nothing.
    $("#fr_signup").submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            // Parses signup response `$data` and updates validation/success UI; returns nothing.
            success: function(data)
            {
                console.log(data);
                jsonData = JSON.parse(data);
                console.log(jsonData['usernameValid']);
                if(jsonData['usernameValid'] == false){
                    $("#username_error").removeClass("hidden");
                }
                 if(jsonData['usernameValid'] == true){
                    $("#username_error").addClass("hidden");
                }
                 if(jsonData['emailUnique'] == false){
                    $("#username_not_unique").removeClass("hidden");
                }
                if(jsonData['passValid'] == false){
                    $("#pass_error").removeClass("hidden");
                }
                 if(jsonData['passValid'] == true){
                    $("#pass_error").addClass("hidden");
                }
                if(jsonData['success'] == true){
                    
                    $("#signupForm").removeClass("authenticated").addClass("hidden");
                    $("#loginForm").removeClass("hidden").addClass("authenticated");
                }
            },
            // Logs signup request failure using `$xhr`, `$status`, and `$error`; returns nothing.
            error: function(xhr, status, error)
            {
                console.log(error);
            }
            });
     });

     /* New Game Handler */
    // Prevents native submission and sends new-game form data; uses `$e` and returns nothing.
    $('#new_game_form').submit(function(e) {
        e.preventDefault();
        console.log($(this).serialize());
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            // Parses `$data` and switches to the game-link view on success; returns nothing.
            success: function(data)
            {
               console.log(data);
               jsonData = JSON.parse(data);
               if(jsonData['success']){
                    $("#newGame").toggleClass('hidden');
                    $("#sendId").toggleClass('hidden');
                    $("#linkId").toggleClass('hidden');
               }
            },
            // Logs new-game request failure using `$xhr`, `$status`, and `$error`; returns nothing.
            error: function(xhr, status, error)
                {
                    console.log(error);
                }
            });
     });

    // Prevents native submission and requests a new target score; uses `$e` and returns nothing.
    $('#nextTargetForm').submit(function(e) {
        e.preventDefault();
        $('#targetScoreError').text('');
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: $(this).serialize(),
            // Parses `$data`, updates both fortresses, and restarts polling or displays its error; returns nothing.
            success: function(data) {
                var jsonData = JSON.parse(data);
                if (jsonData.error) {
                    $('#targetScoreError').text(jsonData.error);
                    return;
                }
                updatePlayer(data, true);
                updateOpponent(data);
                startGamePolling();
            },
            // Shows a target-update failure message; takes no arguments and returns nothing.
            error: function() {
                $('#targetScoreError').text('Unable to update the target score. Please try again.');
            }
        });
    });

    // Sends the end-game action on click; uses the event context and returns nothing.
    $('#endGameButton').on('click', function() {
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: {end_game: 1, handlerId: $('#outcomeHandlerId').val()},
            // Parses `$data`, updates game state, and clears the active handler on success; returns nothing.
            success: function(data) {
                var jsonData = JSON.parse(data);
                if (jsonData.error) {
                    $('#targetScoreError').text(jsonData.error);
                    return;
                }
                updatePlayer(data, true);
                updateOpponent(data);
                sessionStorage.removeItem('handlerId');
                sessionStorage.removeItem('hId');
                if (gamePoll) {
                    clearInterval(gamePoll);
                    gamePoll = null;
                }
            },
            // Shows an end-game failure message; takes no arguments and returns nothing.
            error: function() {
                $('#targetScoreError').text('Unable to end the game. Please try again.');
            }
        });
    });

    // Clears the selected handler and requests the player's game list; uses the event context and returns nothing.
    $('#returnToGames').on('click', function() {
        sessionStorage.removeItem('handlerId');
        sessionStorage.removeItem('hId');
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            dataType: 'json',
            data: {restore_login: 1},
            // Renders the restored game choices from `$jsonData`; returns nothing.
            success: function(jsonData) {
                renderLoggedInView(jsonData, false);
            }
        });
    });

     /* Attack Handler */
    // Prevents native submission and sends an attack while the game is active; uses `$e` and returns nothing.
    $('#player_attackForm').submit(function(e) {
        e.preventDefault();
        if (attackInFlight || currentGameStatus !== 'active') {
            return;
        }
        attackInFlight = true;
        gameStateRevision++;
        refreshActionControls();
        $('#player_error').text('Attack in progress...');
        var attackFailed = false;
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            dataType: 'json',
            data: $(this).serialize(),
            // Handles attack response `$jsonData` and updates both fortresses; returns nothing.
            success: function(jsonData) {
                if (jsonData.error) {
                    attackFailed = true;
                    $('#player_error').text(jsonData.error);
                    return;
                }
                var response = JSON.stringify(jsonData);
                if (updatePlayer(response, false)) {
                    updateOpponent(response);
                } else {
                    attackFailed = true;
                }
            },
            // Reports a failed attack using `$xhr`, `$status`, and `$error`; returns nothing.
            error: function(xhr, status, error) {
                attackFailed = true;
                $('#player_error').text('Attack request failed. Refreshing game state.');
                console.error('Attack request failed:', status, error);
            },
            // Clears the attack in-flight state and resumes polling; takes no arguments and returns nothing.
            complete: function() {
                attackInFlight = false;
                refreshActionControls();
                if (currentGameStatus === 'active') {
                    startGamePolling();
                    if (attackFailed && !gamePollInFlight) {
                        refreshGameState();
                    }
                }
            }
        });
     });

    // Stops polling, clears session IDs and resets login/game UI; uses shared state and returns nothing.
    function resetLoggedInView(){
        if(gamePoll){
            clearInterval(gamePoll);
            gamePoll = null;
        }
        gamePollInFlight = false;
        attackInFlight = false;
        gameStateRevision++;
        currentGameStatus = 'active';
        // Removes each session-storage key in `$key`; returns nothing.
        ['playerId', 'pId', 'userid', 'handlerId', 'hId'].forEach(function(key){
            sessionStorage.removeItem(key);
        });

        $('#fr_login').trigger('reset');
        $('#loginForm').removeClass('hidden');
        $('#signupForm').addClass('hidden');
        $('#login_error, #logout_error').text('').addClass('hidden');
        $('#linkId, #newGame, #gameOptions, #sendId, #gameOutcome, #gameEnded, #targetScoreStatus').addClass('hidden');
        $('.player, .monitor, .gamelog').addClass('hidden');
        $('#options_fieldset .option').remove();
        $('#gameLog').empty();
    }

    // Sends the logout request on click; uses the event context and returns nothing.
    $('#logoutButton').on('click', function(){
        $('#logoutButton').prop('disabled', true);
        $('#logout_error').text('').addClass('hidden');
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            dataType: 'json',
            data: {logout: 1},
            // Resets the logged-in view when `$response` confirms logout; returns nothing.
            success: function(response){
                if(!response || !response.success){
                    $('#logout_error').text('Logout failed. Please try again.').removeClass('hidden');
                    return;
                }
                resetLoggedInView();
            },
            // Displays a logout failure; takes no arguments and returns nothing.
            error: function(){
                $('#logout_error').text('Logout failed. Please try again.').removeClass('hidden');
            },
            // Re-enables the logout button after the request; takes no arguments and returns nothing.
            complete: function(){
                $('#logoutButton').prop('disabled', false);
            }
        });
    });

           /* Link Friend Id */
        // Prevents native friend-link submission and sends the form; uses `$e` and returns nothing.
      $('#link_friend_form').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            // Updates opponent display from response `$data`; returns nothing.
            success: function(data)
            {
               
                updateOpponent(data);
                /* $("." + data).addClass("authenticated");
                $(".gamelog").addClass("authenticated");
                $(".monitor").addClass("authenticated");
                $("#signupForm").addClass("hidden");
                $("#hId").val(data['hId']); */
            },
            // Logs link request failure using `$xhr`, `$status`, and `$error`; returns nothing.
            error: function(xhr, status, error)
            {
                console.log(error);
            }
            });

     });

             /* Choose option */
              // Prevents native game-choice submission and loads the selected game; uses `$e` and returns nothing.
      $('#game_options').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            // Updates player/opponent state and starts polling from response `$data`; returns nothing.
            success: function(data)
            {
                    updatePlayer(data, true);
               updateOpponent(data);
               var jsonData = JSON.parse(data);
               sessionStorage.setItem('hId', jsonData['handlerId']);
                    startGamePolling();
                /* $("." + data).addClass("authenticated");
                $(".gamelog").addClass("authenticated");
                $(".monitor").addClass("authenticated");
                $("#signupForm").addClass("hidden");
                $("#hId").val(data['hId']); */
            },
            // Logs game-choice request failure using `$xhr`, `$status`, and `$error`; returns nothing.
            error: function(xhr, status, error)
            {
                console.log(error);
            }
            });

     });







      
    // Requests legacy fortress state and renders both fortresses and log entries; uses shared DOM/session state and returns nothing.
    function updateData(){
        $.ajax({
          type: "POST",
          url: 'handler.php',
          data: $('#fr_fort').serialize(),
          // Parses response `$data` and renders player, opponent, and log state; returns nothing.
          success: function(data)
          {
              var jsonData = JSON.parse(data);
              var playerData = JSON.parse(jsonData['player']);
              var playerArmory = JSON.parse(playerData['jsonArmory']);
              var opponentData = JSON.parse(jsonData['opponent']);
              var opponentArmory = JSON.parse(opponentData['jsonArmory']);
              var log = jsonData['log']; 
              if (data != null)
                  {
                       
                      /* Display fortress one values */
                      updatePlayer(data)
  
                      /* Display fortress two values */
                      $('#opponent_points').val(opponentData['points']);
                      $('#opponent_heading').text(opponentData['name']);
                      $('#opponent_shopName').text(opponentData['name'] + ' Shop');
                      $('#opponent_flak').val(opponentData['flak']);
                      $('#opponent_cladding').val(opponentData['cladding']);
                      $("#opponent_rockets").empty();
                      $("#opponent_rockets").append("<option>Fire a rocket</option>");
                     /* $.each(opponentArmory, function( key, value ) {  
                          $("#opponent_rockets").append("<option value='" + value["id"] + "'>" + value["name"] + "</option>"); 
                      });*/
  
                      /* Display Game Log */
                      // Appends each log `$value` at `$key`; returns nothing.
                      $.each( log, function( key, value ) {
                         $("#gameLog").append("<p>" + value + "</p>");
                      }); 
                  }
                  else
                  {
                      alert('Invalid Credentials!');
                  }
         },
         // Logs refresh failure using `$xhr`, `$status`, and `$error`; returns nothing.
         error: function(xhr, status, error)
         {
             console.log(error);
         }
     });
    }


    /* Poll for opponent updates */
    /* var opponent = setInterval(updateData, 1000); */



// Parses `$jsonString`, traverses its values, and logs the collected elements; returns nothing.
function readJsonFile(jsonString) {
    jsonString = JSON.stringify(jsonString);
    let jsonObj = JSON.parse(jsonString);
    let jsonElements = [];
    jsonElements = traverse(jsonObj, jsonElements);
    console.log(jsonElements)
}

// Recursively visits `$jsonObj`, appends entries to `$jsonElements`, and returns the accumulated array.
function traverse(jsonObj, jsonElements) {
    if (jsonObj !== null && typeof jsonObj == "object") {
       
        // Visits each `[key, value]` pair, recursively adding nested values to `jsonElements`; returns nothing.
        Object.entries(jsonObj).forEach(([key, value]) => {
            
            if (typeof value == "object") {
                var obj = [];
                let map = new Map();
                map.set(key, traverse(value, obj))
                jsonElements.push(map);
            } else {
                var obj = [];
                obj.key = key;
                obj.value = value;
                jsonElements.push(obj);
            }
        });
    } else {

    }
    return jsonElements;
}

      
    // Requests initial game state and renders fortress/log data; uses shared form state and returns nothing.
    function initializeGame(){
      $.ajax({
        type: "POST",
        url: 'handler.php',
        data: $('#fr_fort').serialize(),
        // Parses response `$data` and updates player/opponent/log UI; returns nothing.
        success: function(data)
        {
           
            if (data != null)
                {
                    console.log(data);
                   var jsonData = JSON.parse(data);
                    var log = jsonData['log']; 
                    updatePlayer(jsonData);
                    updateOpponent(jsonData);
                    // Display Game Log 
                    // Appends each log `$value` at `$key`; returns nothing.
                    $.each( log, function( key, value ) {
                       $("#gameLog").append("<p>" + value + "</p>");
                    }); 
                }
                else
                {
                    alert('Invalid Credentials!');
                }
    },
    // Logs initialization failure using `$xhr`, `$status`, and `$error`; returns nothing.
    error: function(xhr, status, error)
       {
           console.log(error);
       }
   });

}; /* End initializeGame */



          /* Initialize game on p */
      // Prevents native fortress-form submission and requests game state; uses `$e` and returns nothing.
      $('#fr_fort').submit(function(e) {
        e.preventDefault();
        $.ajax({
        type: "POST",
        url: 'handler.php',
        data: $(this).serialize(),
        // Updates player UI from response `$data`; returns nothing.
        success: function(data)
        {
           
            if (data != null)
                {
                    console.log(data);
                   // var log = jsonData['log']; 
                    updatePlayer(data);
                    //updateOpponent(jsonData);
                    
                }
                else
                {
                    alert('Invalid Credentials!');
                }
    },
    // Logs fortress-form request failure using `$xhr`, `$status`, and `$error`; returns nothing.
    error: function(xhr, status, error)
       {
           console.log(error);
       }
   });

     });
    

            /* Restore the logged-in view after a browser refresh. */
            // Restores session-backed login/game UI and refreshes an active game; returns nothing.
            function restoreLoggedInView(){
        var persistedPlayerId = sessionStorage.getItem('playerId') || sessionStorage.getItem('pId') || sessionStorage.getItem('userid');
        var persistedHandlerId = sessionStorage.getItem('handlerId') || sessionStorage.getItem('hId');
        if (!persistedPlayerId) {
            return;
        }

        $('#loginForm, #signupForm').addClass('hidden');
        $('#link_friend_form_playerId, #options_playerId').val(persistedPlayerId);
        if (persistedHandlerId) {
            sessionStorage.setItem('playerId', persistedPlayerId);
            sessionStorage.setItem('handlerId', persistedHandlerId);
            $('#linkId').removeClass('hidden');
            $('#newGame, #gameOptions').addClass('hidden');
            $('.player, .monitor, .gamelog').removeClass('hidden');
            refreshGameState();
            startGamePolling();
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'handler.php',
            dataType: 'json',
            data: {restore_login: 1},
            // Renders restored account/game data from `$jsonData`; returns nothing.
            success: function(jsonData) {
                renderLoggedInView(jsonData, false);
            },
            // Clears stale player IDs after a restore failure; takes no arguments and returns nothing.
            error: function() {
                sessionStorage.removeItem('playerId');
                sessionStorage.removeItem('pId');
            }
        });
        }
        restoreLoggedInView();





  

     /* Gun Shops */
    // Prevents native submission and sends serialized gun-shop data as a turn action; uses `$e` and returns nothing.
     $('#player_gunForm').submit(function(e) {
        e.preventDefault();
        submitTurnAction(turnActionData(this), 'Rocket purchase failed.');
     });

  

    // Parses `$data` into local player/opponent/log variables; currently performs no UI updates and returns nothing.
    function update(data){
        var jsonData = JSON.parse(data);
        var playerData = JSON.parse(jsonData['player']);
        var playerArmory = JSON.parse(playerData['jsonArmory']);
        var opponentData = JSON.parse(jsonData['opponent']);
        var log = jsonData['log']; 

        /* Update Log */

        /* Update player */

        /* Update opponent */
     }

    // Finds the first JSON object in `$response` and returns the substring starting before its opening brace, or nothing if absent.
    function cleanResponse(response){
        
        var len = response.length;
        for(let x = 0; x < len; x++){
            if(response.charAt(x) == '{'){
                x--;
                clean = response.substring(x);
                return clean;
              
                break;
            }
        }
    }

     /* Cladding Shop */
    // Prevents native submission and sends serialized cladding-shop data as a turn action; uses `$e` and returns nothing.
     $('#player_claddingForm').submit(function(e) {
        e.preventDefault();
        submitTurnAction(turnActionData(this), 'Cladding purchase failed.');

     });



     /* Flak Shop */
    // Prevents native submission and sends serialized flak-shop data as a turn action; uses `$e` and returns nothing.
     $('#player_flakForm').submit(function(e) {
        e.preventDefault();
          submitTurnAction(turnActionData(this), 'Flak purchase failed.');

     });




    

  

     /* Link to Friend's Id */

        // Prevents native friend-ID submission and sends the form data; uses `$e` and returns nothing.
      $('#friend_id').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            // Parses response `$data` and updates player-facing fortress labels; returns nothing.
            success: function(data)
            {
                
                var jsonData = JSON.parse(cData);
                var playerData = JSON.parse(jsonData['player']);
              
                $('#player_heading').text(playerData['name']);
                $('#player_shopName').text(playerData['name'] + ' Shop');
            },
            // Logs friend-ID request failure using `$xhr`, `$status`, and `$error`; returns nothing.
            error: function(xhr, status, error)
            {
                console.log(error);
            }
            });

     });

     /* Name Changes */

    // Prevents native name-form submission and sends the change request; uses `$e` and returns nothing.
     $('#player_nameForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            // Updates player UI from response `$data`; returns nothing.
            success: function(data)
            {
                updatePlayer(data);
            },
            // Logs name-change request failure using `$xhr`, `$status`, and `$error`; returns nothing.
            error: function(xhr, status, error)
            {
                console.log(error);
            }
            });

     });

     

     






    });
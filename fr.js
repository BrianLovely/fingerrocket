$(document).ready(function() {

    $( document ).on( "ajaxError", function() {
        $( ".error" ).text( "Triggered ajaxError handler." );
      } );

      $("#login a").on("mouseup", function(){
        $("#loginForm").removeClass("authenticated").addClass("hidden");
        $("#signupForm").removeClass("hidden").addClass("authenticated");
      });

      $("#signupForm a").on("mouseup", function(){
        $("#signupForm").removeClass("authenticated").addClass("hidden");
        $("#loginForm").removeClass("hidden").addClass("authenticated");
      });

            $( "#workshopToggle" ).on( "click", function(event) {
                event.preventDefault();
        $("#inner").toggleClass("hidden");
    } );

      function setPlayerId(pId){
        $("#initPlayerId").val(pId);
        $("#nameChangePlayerId").val(pId);
        $("#flakPlayerId").val(pId);
        $("#claddingPlayerId").val(pId);
        $("#gunshopPlayerId").val(pId);
        $("#attackPlayerId").val(pId);
        
      }

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
            var isPlayerTurn = playerData['playerUp'] === playerData['player']['id'];
            $('#player_attack').prop('disabled', !isPlayerTurn || gameStatus !== 'active');
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
        $.each( playerArmory, function( key, value ) {
            $("#player_rockets").append("<option value='" + value["id"] + "'>" + value["name"] + "</option>");
        }); 
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
        return true;
    }

    function updateWorkshop(items){
        $("#rocket_parts, #cladding_parts, #blueprints, #crafted_items").find("p, button.blueprint").remove();
        if (!Array.isArray(items)) {
            return;
        }
        if(items[0].length > 0){
            $.each( items[0], function( key, value ) {
                $("#rocket_parts").append("<p>" + (value.name || value) + "</p>");
            });
        }
        if(items[1].length > 0){
            $.each( items[1], function( key, value ) {
                $("#cladding_parts").append("<p>" + (value.name || value) + "</p>");
            });
        }
        if(items[2].length > 0){
            $.each( items[2], function( key, value ) {
                var blueprintId = value.id || '';
                var blueprintName = value.module || value.name || value;
                $("#blueprints").append("<button type='button' class='blueprint' data-blueprint-id='" + blueprintId + "'>Craft " + blueprintName + "</button>");
            });
        }
        if(items[3] && items[3].length > 0){
            $.each(items[3], function(key, value){
                $("#crafted_items").append("<p>" + (value.name || value) + "</p>");
            });
        }
    }

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

    function appendLogEntries(entries){
        $.each(entries, function(key, value){
            $("#gameLog").append("<p>" + value + "</p>");
        });
    }

    function updateLog(log, replaceLog){
        if (!Array.isArray(log)) {
            return;
        }
        if (replaceLog && !logInitialized) {
            $("#gameLog").empty();
            $.each(log, function(key, value){
                $("#gameLog").append("<p>" + value + "</p>");
            });
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

    function appendOption($data){
    }

    var gamePoll;
    var gamePollInFlight = false;

    function refreshGameState(){
        var playerId = sessionStorage.getItem('playerId');
        var handlerId = sessionStorage.getItem('handlerId') || sessionStorage.getItem('hId');
        if (!playerId || !handlerId || gamePollInFlight) {
            return;
        }
        gamePollInFlight = true;
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: {refresh_playerId: playerId, refresh_handlerId: handlerId},
            success: function(data){
                var jsonData = JSON.parse(data);
                if (!jsonData['error'] && updatePlayer(data, true)) {
                    updateOpponent(data);
                }
            },
            complete: function(){
                gamePollInFlight = false;
            }
        });
    }

    function startGamePolling(){
        if (gamePoll) {
            clearInterval(gamePoll);
        }
        gamePoll = setInterval(refreshGameState, 1000);
    }

    $(document).on('click', '.blueprint', function(){
        var blueprintId = $(this).data('blueprint-id');
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: {craft_blueprint_id: blueprintId},
            success: function(data){
                var jsonData = JSON.parse(data);
                if (!jsonData['craft'] || !jsonData['craft']['success']) {
                    var craftError = jsonData['craft'] && jsonData['craft']['error'];
                    $('#player_error').text(craftError || 'Blueprint crafting failed.');
                    return;
                }
                updatePlayer(data, true);
                updateOpponent(data);
            },
            error: function(){
                $('#player_error').text('Blueprint crafting failed.');
            }
        });
    });

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
    $('#fr_login').submit(function(e) {
        e.preventDefault();
        $('#login_error').text('').addClass('hidden');

        $.ajax({
            type: 'POST',
            url: 'handler.php',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(jsonData) {
                if (!jsonData || !jsonData.playerExists) {
                    $('#login_error').text('Invalid email or password.').removeClass('hidden');
                    return;
                }
                renderLoggedInView(jsonData, true);
            },
            error: function(xhr, status, error) {
                console.error('Login request failed:', status, error);
                $('#login_error').text('Unable to complete login. Please try again.').removeClass('hidden');
            }
        });
    });
    

  $("#fr_signup").submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
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
            }, error: function(xhr, status, error)
            {
                console.log(error);
            }
            });
     });

     /* New Game Handler */
    $('#new_game_form').submit(function(e) {
        e.preventDefault();
        console.log($(this).serialize());
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            success: function(data)
            {
               console.log(data);
               jsonData = JSON.parse(data);
               if(jsonData['success']){
                    $("#newGame").toggleClass('hidden');
                    $("#sendId").toggleClass('hidden');
                    $("#linkId").toggleClass('hidden');
               }
            }, error: function(xhr, status, error)
                {
                    console.log(error);
                }
            });
     });

    $('#nextTargetForm').submit(function(e) {
        e.preventDefault();
        $('#targetScoreError').text('');
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: $(this).serialize(),
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
            error: function() {
                $('#targetScoreError').text('Unable to update the target score. Please try again.');
            }
        });
    });

    $('#endGameButton').on('click', function() {
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: {end_game: 1, handlerId: $('#outcomeHandlerId').val()},
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
            error: function() {
                $('#targetScoreError').text('Unable to end the game. Please try again.');
            }
        });
    });

    $('#returnToGames').on('click', function() {
        sessionStorage.removeItem('handlerId');
        sessionStorage.removeItem('hId');
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            dataType: 'json',
            data: {restore_login: 1},
            success: function(jsonData) {
                renderLoggedInView(jsonData, false);
            }
        });
    });

     /* Attack Handler */
    $('#player_attackForm').submit(function(e) {
        e.preventDefault();
        console.log($(this).serialize());
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            success: function(data)
            {
               var jsonData = JSON.parse(data);
               if (jsonData['error']) {
                   $('#player_error').text(jsonData['error']);
                   if (jsonData['error'].indexOf('no longer available') !== -1) {
                       refreshGameState();
                   }
                   return;
               }
               if (updatePlayer(data, false)) {
                   updateOpponent(data);
               }
            }, error: function(xhr, status, error)
                {
                    console.log(error);
                }
            });
     });

           /* Link Friend Id */
      $('#link_friend_form').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            success: function(data)
            {
               
                updateOpponent(data);
                /* $("." + data).addClass("authenticated");
                $(".gamelog").addClass("authenticated");
                $(".monitor").addClass("authenticated");
                $("#signupForm").addClass("hidden");
                $("#hId").val(data['hId']); */
            }, error: function(xhr, status, error)
            {
                console.log(error);
            }
            });

     });

             /* Choose option */
      $('#game_options').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
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
            }, error: function(xhr, status, error)
            {
                console.log(error);
            }
            });

     });







      
      function updateData(){
        $.ajax({
          type: "POST",
          url: 'handler.php',
          data: $('#fr_fort').serialize(),
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
                      $.each( log, function( key, value ) {
                         $("#gameLog").append("<p>" + value + "</p>");
                      }); 
                  }
                  else
                  {
                      alert('Invalid Credentials!');
                  }
         }, error: function(xhr, status, error)
         {
             console.log(error);
         }
     });
    }


    /* Poll for opponent updates */
    /* var opponent = setInterval(updateData, 1000); */



function readJsonFile(jsonString) {
    jsonString = JSON.stringify(jsonString);
    let jsonObj = JSON.parse(jsonString);
    let jsonElements = [];
    jsonElements = traverse(jsonObj, jsonElements);
    console.log(jsonElements)
}

function traverse(jsonObj, jsonElements) {
    if (jsonObj !== null && typeof jsonObj == "object") {
       
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

      
      function initializeGame(){
      $.ajax({
        type: "POST",
        url: 'handler.php',
        data: $('#fr_fort').serialize(),
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
                    $.each( log, function( key, value ) {
                       $("#gameLog").append("<p>" + value + "</p>");
                    }); 
                }
                else
                {
                    alert('Invalid Credentials!');
                }
       }, error: function(xhr, status, error)
       {
           console.log(error);
       }
   });

}; /* End initializeGame */



          /* Initialize game on p */
      $('#fr_fort').submit(function(e) {
        e.preventDefault();
        $.ajax({
        type: "POST",
        url: 'handler.php',
        data: $(this).serialize(),
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
       }, error: function(xhr, status, error)
       {
           console.log(error);
       }
   });

     });
    

      /* Restore the logged-in view after a browser refresh. */
      $(window).on('load', function() {
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
            success: function(jsonData) {
                renderLoggedInView(jsonData, false);
            },
            error: function() {
                sessionStorage.removeItem('playerId');
                sessionStorage.removeItem('pId');
            }
        });
     });





  

     /* Gun Shops */
     $('#player_gunForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            success: function(data)
            {
                updatePlayer(data);
                updateOpponent(data);
                
            }, error: function(xhr, status, error)
            {
                console.log(error);
            }
            });
     });

  

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
     $('#player_claddingForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            success: function(data)
            {
                updatePlayer(data);
                updateOpponent(data);
                
            }, error: function(xhr, status, error)
            {
                console.log(error);
            }
            });

     });



     /* Flak Shop */
     $('#player_flakForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            success: function(data)
            {
               updatePlayer(data);
               updateOpponent(data);
            }, error: function(xhr, status, error)
            {
                console.log(xhr);
            }
            });

     });




    

  

     /* Link to Friend's Id */

      $('#friend_id').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            success: function(data)
            {
                
                var jsonData = JSON.parse(cData);
                var playerData = JSON.parse(jsonData['player']);
              
                $('#player_heading').text(playerData['name']);
                $('#player_shopName').text(playerData['name'] + ' Shop');
            }, error: function(xhr, status, error)
            {
                console.log(error);
            }
            });

     });

     /* Name Changes */

     $('#player_nameForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'handler.php',
            data: $(this).serialize(),
            success: function(data)
            {
                updatePlayer(data);
            }, error: function(xhr, status, error)
            {
                console.log(error);
            }
            });

     });

     

     






    });
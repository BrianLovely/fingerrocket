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

      $( "#workshopToggle" ).on( "mouseup", function() {
        //alert("hey");
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
        var isPlayerTurn = playerData['playerUp'] === playerData['player']['id'];
        $('#player_attack').prop('disabled', !isPlayerTurn);
        if (!isPlayerTurn) {
            $('#player_error').text('Wait for the other player to take their turn.');
        } else {
            $('#player_error').text('');
        }
        sessionStorage.setItem('playerId', playerData['player']['id']);
        sessionStorage.setItem('handlerId', playerData['handlerId']);
        var playerArmory = playerData['f1']['armory'];
        var log = playerData['log'];
        var items = JSON.parse(playerData['player']['items'] || '[]');
        console.log("items: " + items[1]);
        updateWorkshop(items);
        updateLog(log, replaceLog);
       $('#link_friend_form_playerId').val(playerData['player']['id']);
        $('#player_points').val(playerData['f1']['points']);
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


    var logQueue = [];
    var logTimer = null;
    var knownLogLength = 0;
    var logInitialized = false;

    function displayNextLogEntry(){
        if(logQueue.length === 0){
            logTimer = null;
            return;
        }
        $("#gameLog").append("<p>" + logQueue.shift() + "</p>");
        logTimer = setTimeout(displayNextLogEntry, 10000);
    }

    function queueLogEntries(entries){
        logQueue = logQueue.concat(entries);
        if(logTimer === null){
            displayNextLogEntry();
        }
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
            var newCount = log.length - knownLogLength;
            if(newCount > 0){
                queueLogEntries(log.slice(0, newCount).reverse());
            }
            knownLogLength = log.length;
            return;
        }
        if(log.length > 0){
            queueLogEntries(log.slice().reverse());
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

      /* Log In */
        $('#fr_login').submit(function(e) {  
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
                    $("#loginForm").addClass('hidden');
                    $("#linkId").removeClass('hidden');
                    
                    jsonData = JSON.parse(data);
                    console.log(jsonData['fortresses']);
                    sessionStorage.setItem('pId', jsonData['playerId']);
                    $("#options_playerId").val(jsonData['playerId']);
                    if(jsonData['playerExists'] && jsonData['handlerExists']){
                        //If player is in database and there is a handler, load existing games
                        $(".player").removeClass('hidden');
                        $(".monitor").removeClass('hidden');
                        $(".gameLog").removeClass('hidden');
                        $("#gameOptions").removeClass('hidden');
                        $(".account_switch").addClass('hidden');
                        fData = jsonData['fortresses'];
                        if (Array.isArray(fData)) {
                            $.each(fData, function(key, value) {
                                $("#options_fieldset").append("<div class='option'><input type='radio' name='options_handlerId' id='" + key + "' value='" + value['handlerId'] + "'/>" + "<label for='" + key + "'>" + value['playerFortressName'] + " with " + value['playerFortressPoints'] + " points, versus " + value['opponentFortressName'] + " with " + value['opponentFortressPoints'] + " points</label></div>");
                            });
                        } else if (fData && typeof fData === 'object') {
                            var key = 0;
                            $("#options_fieldset").append("<div class='option'><input type='radio' name='options_handlerId' id='" + key + "' value='" + fData['handlerId'] + "'/>" + "<label for='" + key + "'>" + fData['playerFortressName'] + " with " + fData['playerFortressPoints'] + " points, versus " + fData['opponentFortressName'] + " with " + fData['opponentFortressPoints'] + " points</label></div>");
                        }
                    }
                    if(jsonData['playerExists'] && !jsonData['handlerExists']){
                        //If player is in database but no handler exists, show new game button
                        console.log("playerId: " + jsonData['playerId'] );
                        $("#new_game_playerId").val(jsonData['playerId'] );
                        $("#newGame").removeClass('hidden');
                    }
                    //var log = jsonData['log']; 
                    //updatePlayer(data);
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
    

      /* Call initializeGame on Page Load */
      $(window).on("load", function(e) {
        e.preventDefault();
        /* Display interface while session persists */
        var persistedPlayerId = sessionStorage.getItem('playerId') || sessionStorage.getItem('pId') || sessionStorage.getItem('userid');
        if(persistedPlayerId != null){
            var userid = persistedPlayerId;
            $("." + userid).addClass("authenticated");
            $("#uid").text(userid);
            $(".gamelog").addClass("authenticated");
            $(".monitor").addClass("authenticated");
            $(".login").addClass("hidden");
            $("#hId").val(sessionStorage.getItem('hId'));
            $("#loginForm").addClass('hidden');
            $("#signupForm").addClass('hidden');
            if (sessionStorage.getItem('handlerId') || sessionStorage.getItem('hId')) {
                startGamePolling();
            }
        }
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
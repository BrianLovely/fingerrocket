$(document).ready(function() {
    var rString = new String(
        '<br /><b>Warning</b>:  Undefined property: FingerRocket::$0 in <b>/home/studiobl/www/fingerrocket/fortress.php</b> on line <b>446</b><br />{"f1":"{\"id\":\"f1\",\"flak\":\"0\",\"jsonArmory\":\"[]\",\"cladding\":\"Wood\",\"name\":\"The Red Redoubt\",\"points\":\"370\"}","f1_damRes":0,"f1_clad":"0","f2":"{\"id\":\"f2\",\"flak\":\"0\",\"jsonArmory\":\"[]\",\"cladding\":\"Wood\",\"name\":\"The Eagle's Aerie\",\"points\":103}","playerUp":"f2","log":["The Eagle's Aerie is awarded 41 points!","The Red Redoubt's Wood cladding takes 30 damage!","It's a hit!","Rocket toHit: 50 + D10 roll: 9 Total: 59","Defender resistance D10 roll: 2 + damageRes: 5 + Cladding: 0 Total: 7","The Eagle's Aerie attacks The Red Redoubt with a Bolt!"]}'
        );
    
      

        function cleanResponse(response){
            console.log(response.length)
            var len = response.length;
            for(let x = 0; x < len; x++){
                console.log('character: ' + response.charAt(x));
                if(response.charAt(x) == '{'){
                    x--;
                    clean = response.substring(x);
                    console.log(clean);
                    break;
                }
            }
        }

        cleanResponse(rString);
});


window.onload = function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('error') === 'player_not_found') {
        alert('Player not Registered!');

        window.history.replaceState({}, document.title, window.location.pathname);
    }
    else if (params.get('error') === 'password_incorrect') {
        alert('Incorrect Paassword!');

        window.history.replaceState({}, document.title, window.location.pathname);
    }
    else if (params.get('error') === 'Wrong_player_name') {
        alert('Player Name is incorrect');

        window.history.replaceState({}, document.title, window.location.pathname);
    }
}
function validateForm(event){
    let password = document.registrationForm.Player.value;
    let player = document.registrationForm.password.value;
    let submitter = event.submitter;
    let isValid = true
   
    printError("passwordHeader", "Password");
    printError("playerHeader","Player Name");
   
    console.log(`${player} player :   EVENT  ${submitter.value}`);
    if(password == ""){
        // printError("passwordHeader","please don't leave password blank");
        alert("Please don't leave password field blank");
        isValid = false;
        console.log("errror password");
    }
    if(player ==""){
        // printError("playerHeader","please don't leave Player blank");
        alert("Please don't leave Player field blank");
        isValid = false;
    }
    
    else{
        
            printError("passwordHeader", "Password");
            printError("playerHeader","Player Name");
            if(submitter.value == "login"){
               
                document.registrationForm.action ="loginForm.php";
                
        
            }
            else if (submitter.value == "register"){
                    
                    document.registrationForm.action = "registerForm.php";
                    
            }

        
    }
   
    return isValid;
}function printError(elemId, hintMsg) {
    document.getElementById(elemId).innerHTML = hintMsg;
}
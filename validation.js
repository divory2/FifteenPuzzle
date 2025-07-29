


function validateForm(){
    let password = document.registrationForm.Player.value;
    let player = document.registrationForm.password.value;
    let isValid = true
    printError("passwordHeader", "Password");
    printError("playerHeader","Player Name");
    console.log(`${player} player :   Password  ${password}`);
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
        if((formPasswordError && formPlayerError)){
            printError("passwordHeader", "Password");
            printError("playerHeader","Player Name");

        }
    }
    return isValid;
}function printError(elemId, hintMsg) {
    document.getElementById(elemId).innerHTML = hintMsg;
}
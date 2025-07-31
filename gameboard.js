const selector = document.getElementById('backgroundSelector');
  const preview = document.getElementById('backgroundPreview');
    const selectedUploadURL= document.getElementById('url');
    console.log(`url outside of function in input before trim ${selectedUploadURL.value}`);
    const previewUpload= document.getElementById('uploadedPreview');
  selector.addEventListener('change', function () {
    const selectedImage = this.value;
    if (selectedImage) {
      preview.src = selectedImage;
      preview.style.display = 'block';
    } else {
      preview.style.display = 'none';
    }
  });

// let uploadImage = document.getElementById('upload');
// uploadImage.addEventListener('load');
function startGame(event){
    event.preventDefault();
    let submittedButton =event.submitter
    console.log(`url in input before trim ${selectedUploadURL}`);
    const imageUrl = selectedUploadURL.value.trim();
    console.log(`image Url input ${imageUrl}`);
    console.log(`submitted Button  ${submittedButton}`);
    console.log(`submitted Button Value ${submittedButton.value}`);
    const add = document.getElementById('add');
    const no = document.getElementById('no');

    if(submittedButton && submittedButton.value == "upload"){
console.log("event triggerd by upload button");
console.log(`url that in input ${imageUrl}`);

        if(imageUrl){
                        
                previewUpload.src = imageUrl
                previewUpload.style.display= 'block';
                
                add.hidden =false;
                no.hidden = false;
                
            }
         else{
                previewUpload.style.display='none';
            }


       

    }
    else if(submittedButton && submittedButton.value=="add"){
            
            
            // <option value="shopping.webp">sample2</option>
            let imageName = prompt("Please enter a name for the image");
            if (imageName === null || imageName.trim() === "") {
                imageName = prompt("Please enter a name for the image");
            }
     
            const addOption = document.createElement('option');
            addOption.value = imageUrl;
            addOption.textContent= imageName;
            selector.appendChild(addOption);
            
            selectedUploadURL.value = "";
            add.hidden = true;
            no.hidden = true;
            previewUpload.src = "";

            
    }   
    

}
// previewUpload.onload() = function (){
//     previewUpload.style.display = 'block';
// }
// previewUpload.onerror() = function(){
//     previewUpload.style.display = 'none';
//     alert("Image failed to load. Check your URL.");
// }
function handleImage(){

    previewUpload.style.display='block';
}
function handleImageError(){
    previewUpload.style.display="none";
    alert("Image failed to load. Check your URL.");
}
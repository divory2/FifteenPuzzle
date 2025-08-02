window.onload = function() {
    const selector = document.getElementById('backgroundSelector');
    const preview = document.getElementById('backgroundPreview');
    const selectedUploadURL = document.getElementById('url');
    const previewUpload = document.getElementById('uploadedPreview');
    const addBtn = document.getElementById('add');
    const noBtn = document.getElementById('no');
  
    let selectedBackgroundUrl = '';
  
    selector.addEventListener('change', function() {
      const selectedImage = this.value;
      if (selectedImage) {
        preview.src = selectedImage;
        preview.style.display = 'block';
      } else {
        preview.style.display = 'none';
      }
    });
  
    previewUpload.addEventListener('load', () => {
      previewUpload.style.display = 'block';
    });
  
    previewUpload.addEventListener('error', () => {
      previewUpload.style.display = 'none';
      alert("Image failed to load. Check your URL.");
    });
  
    window.startGame = function(event) {
      event.preventDefault();
      const submittedButton = event.submitter;
      const imageUrl = selectedUploadURL.value.trim();
  
      if (submittedButton && submittedButton.value === "start") {
        selectedBackgroundUrl = selector.value || imageUrl;
        if (!selectedBackgroundUrl) {
          alert("Please select or upload a background image first.");
          return;
        }
        buildBoard(4);
      }
      else if (submittedButton && submittedButton.value === "upload") {
        if (imageUrl) {
          previewUpload.src = imageUrl;
          addBtn.hidden = false;
          noBtn.hidden = false;
        } else {
          previewUpload.style.display = 'none';
        }
      }
      else if (submittedButton && submittedButton.value === "add") {
        let imageName = prompt("Please enter a name for the image");
        if (imageName && imageName.trim()) {
          const option = document.createElement('option');
          option.value = imageUrl;
          option.textContent = imageName;
          selector.appendChild(option);
          selectedUploadURL.value = "";
          previewUpload.src = "";
          addBtn.hidden = true;
          noBtn.hidden = true;
        }
      }
      else if (submittedButton && submittedButton.value === "no") {
        previewUpload.src = "";
        addBtn.hidden = true;
        noBtn.hidden = true;
      }
    };
  
    function buildBoard(size) {
      const gameBoard = document.getElementById('gameBoard');
      gameBoard.innerHTML = ''; // clear old board
  
      const tileSizePercent = 100 / size;
      gameBoard.style.display = 'flex';
      gameBoard.style.flexWrap = 'wrap';
  
      for (let row = 0; row < size; row++) {
        for (let col = 0; col < size; col++) {
          const tile = document.createElement('div');
          tile.className = `tile tile${row * size + col + 1}`;
          tile.style.width = tileSizePercent + '%';
          tile.style.height = tileSizePercent + '%';
          tile.style.backgroundImage = `url('${selectedBackgroundUrl}')`;
          tile.style.backgroundSize = `${size * 100}% ${size * 100}%`;
          tile.style.backgroundPosition = `-${col * 100 / (size - 1)}% -${row * 100 / (size - 1)}%`;
          tile.style.border = '1px solid black';
          tile.style.boxSizing = 'border-box';
          tile.style.cursor = 'pointer';
  
          tile.addEventListener('click', () => clickTile(row, col));
          gameBoard.appendChild(tile);
        }
      }
    }
  
    function clickTile(row, col) {
      console.log(`Tile clicked at row ${row}, col ${col}`);
    }
  };
  
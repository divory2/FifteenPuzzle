window.onload = function() {
    const selector = document.getElementById('backgroundSelector');
    const preview = document.getElementById('backgroundPreview');
    const selectedUploadURL = document.getElementById('url');
    const previewUpload = document.getElementById('uploadedPreview');
    const addBtn = document.getElementById('add');
    const noBtn = document.getElementById('no');
  
    let selectedBackgroundUrl = '';
    let tiles = []; 
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



        initTiles();
        shuffleTiles();
        buildBoard();
        
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


    function initTiles() {
        tiles = [];
        for (let i = 1; i <= 15; i++) {
          tiles.push(i);
        }
        tiles.push(null); // last tile is empty
      }
      



    function buildBoard() {
        gameBoard.innerHTML = '';
        tiles.forEach((tileNum, idx) => {
          const tile = document.createElement('div');
          tile.classList.add('tile');
          if (tileNum === null) {
            tile.classList.add('empty');
          } else {
            tile.classList.add(`tile${tileNum}`);
            tile.style.backgroundImage = `url('${selectedBackgroundUrl}')`;
            tile.style.backgroundPosition = getBackgroundPosition(tileNum);
          }
          tile.addEventListener('mousedown', () => tryMoveTile(idx));
          gameBoard.appendChild(tile);
        });
      }
      function getBackgroundPosition(tileNum) {
        const size = 4;
        const row = Math.floor((tileNum - 1) / size);
        const col = (tileNum - 1) % size;
      
        // backgroundSize 400% means each tile is 1/4th of the image
        return `${col * 25}% ${row * 25}%`;
      }
      function shuffleTiles() {
        // Fisher-Yates shuffle for array
        for (let i = tiles.length - 1; i > 0; i--) {
          const j = Math.floor(Math.random() * (i + 1));
          [tiles[i], tiles[j]] = [tiles[j], tiles[i]];
        }
      
        // Ensure the puzzle is solvable (optional advanced step)
        if (!isSolvable(tiles)) {
          // swap two tiles (not including empty) to fix solvability
          if (tiles[0] !== null && tiles[1] !== null) {
            [tiles[0], tiles[1]] = [tiles[1], tiles[0]];
          } else {
            [tiles[2], tiles[3]] = [tiles[3], tiles[2]];
          }
        }
      }
      
      // Check solvability (optional, but recommended)
      function isSolvable(arr) {
        const size = 4;
        let invCount = 0;
        const tileList = arr.filter(n => n !== null);
        for (let i = 0; i < tileList.length -1; i++) {
          for (let j = i + 1; j < tileList.length; j++) {
            if (tileList[i] > tileList[j]) invCount++;
          }
        }
        const emptyRow = Math.floor(arr.indexOf(null) / size);
        if (size % 2 === 1) {
          return invCount % 2 === 0;
        } else {
          if ((size - emptyRow) % 2 === 1) {
            return invCount % 2 === 0;
          } else {
            return invCount % 2 === 1;
          }
        }
      }
      
      
      



      
      
  
    function clickTile(row, col) {
      console.log(`Tile clicked at row ${row}, col ${col}`);
    }
  };
  
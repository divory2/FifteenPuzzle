window.onload = function() {
    const selector = document.getElementById('backgroundSelector');
    const preview = document.getElementById('backgroundPreview');
    const selectedUploadURL = document.getElementById('url');
    const previewUpload = document.getElementById('uploadedPreview');
    const addBtn = document.getElementById('add');
    const noBtn = document.getElementById('no');
    previewUpload.style.display = 'none';

    let timerInterval = null;
    let timeElapsed = 0;
    let selectedBackgroundUrl = '';
    let tiles = []; 
    let moveCount = 0;

    const gameBoard = document.getElementById('gameBoard'); 
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
        if (previewUpload.src && previewUpload.src !== window.location.href) {
          previewUpload.style.display = 'block';
        }
      });
  
    previewUpload.addEventListener('error', () => {
      previewUpload.style.display = 'none';
      alert("Image failed to load. Check your URL.");
      addBtn.hidden = true;
      noBtn.hidden = true;
    });
  
    window.startGame = function(event) {
      event.preventDefault();
      const submittedButton = event.submitter;
      const imageUrl = selectedUploadURL.value.trim();
  
      if (submittedButton && submittedButton.value === "start") {
        // Check permission before starting game
        RBAC.executeWithPermission('play_game', function() {
          selectedBackgroundUrl = selector.value || imageUrl;
          if (!selectedBackgroundUrl) {
            alert("Please select or upload a background image first.");
            return;
          }


        initTiles();
        shuffleTiles();
        buildBoard();
        // Reset & start timer
        moveCount = 0;
        timeElapsed = 0;
        updateTimerDisplay();
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            timeElapsed++;
            updateTimerDisplay();
        }, 1000);
        
      }
      else if (submittedButton && submittedButton.value === "upload") {
        // Check permission before uploading
        RBAC.executeWithPermission('upload_images', function() {
          if (imageUrl) {
            previewUpload.src = imageUrl;
            addBtn.hidden = false;
            noBtn.hidden = false;
          } else {
            previewUpload.style.display = 'none';
          }
        }, "You need player permissions to upload images");
      }
      else if (submittedButton && submittedButton.value === "add") {
        let imageName = prompt("Please enter a name for the image");
        if (imageName && imageName.trim()) {
          const option = document.createElement('option');
          option.value = imageUrl;
          option.textContent = imageName;
          selector.appendChild(option);
          selectedUploadURL.value = "";
          previewUpload.style.display = 'none';
          addBtn.hidden = true;
          noBtn.hidden = true;
        }
      }
      else if (submittedButton && submittedButton.value === "no") {
        previewUpload.src = "";
        
        previewUpload.style.display = 'none';
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
      


      function updateTimerDisplay() {
        const timerElement = document.getElementById('timer');
        timerElement.textContent = `Time: ${timeElapsed}s`;
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
          //tile.addEventListener('mousedown', () => tryMoveTile(idx));
          tile.addEventListener('mousedown', (e) => startDrag(e, idx));
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
      
      function checkIfSolved() {
        for (let i = 0; i < 15; i++) {
          if (tiles[i] !== i + 1) return;
        }
        if (tiles[15] === null) {
          clearInterval(timerInterval); // stop timer
          alert(`🎉 You solved the puzzle in ${timeElapsed} seconds!`);
        }
      }
      
      



      
      
  
      function tryMoveTile(idx) {
        const emptyIndex = tiles.indexOf(null);
        const size = 4;
      
        const emptyRow = Math.floor(emptyIndex / size);
        const emptyCol = emptyIndex % size;
        const clickedRow = Math.floor(idx / size);
        const clickedCol = idx % size;
      
        const isAdjacent =
          (clickedRow === emptyRow && Math.abs(clickedCol - emptyCol) === 1) ||
          (clickedCol === emptyCol && Math.abs(clickedRow - emptyRow) === 1);
      
        if (isAdjacent) {
          // swap tiles
          [tiles[idx], tiles[emptyIndex]] = [tiles[emptyIndex], tiles[idx]];
          buildBoard();
          checkIfSolved();
        }
      }

      let dragging = false;
        let startX = 0, startY = 0;

function startDrag(e, idx) {
  dragging = true;
  startX = e.clientX;
  startY = e.clientY;

  function onMove(ev) {
    if (!dragging) return;
    const dx = ev.clientX - startX;
    const dy = ev.clientY - startY;

    const threshold = 30; // px to trigger move

    if (Math.abs(dx) > threshold || Math.abs(dy) > threshold) {
      dragging = false;
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('mouseup', onUp);

      // decide direction
      if (Math.abs(dx) > Math.abs(dy)) {
        if (dx > 0) trySlide(idx, 'right');
        else trySlide(idx, 'left');
      } else {
        if (dy > 0) trySlide(idx, 'down');
        else trySlide(idx, 'up');
      }
    function tryMoveTile(clickedIdx) {
        const emptyIdx = tiles.indexOf(null);
        const size = 4;
        
        const clickedRow = Math.floor(clickedIdx / size);
        const clickedCol = clickedIdx % size;
        const emptyRow = Math.floor(emptyIdx / size);
        const emptyCol = emptyIdx % size;
        
        // Check if clicked tile is adjacent to empty space
        const isAdjacent = (Math.abs(clickedRow - emptyRow) === 1 && clickedCol === emptyCol) ||
                          (Math.abs(clickedCol - emptyCol) === 1 && clickedRow === emptyRow);
        
        if (isAdjacent) {
            // Swap clicked tile with empty space
            [tiles[clickedIdx], tiles[emptyIdx]] = [tiles[emptyIdx], tiles[clickedIdx]];
            buildBoard();
            
            // Check if puzzle is solved
            if (isPuzzleSolved()) {
                setTimeout(() => {
                    alert("Congratulations! You solved the puzzle!");
                }, 100);
            }
        }
    }

    function isPuzzleSolved() {
        for (let i = 0; i < 15; i++) {
            if (tiles[i] !== i + 1) {
                return false;
            }
        }
        return tiles[15] === null;
    }

    function clickTile(row, col) {
      console.log(`Tile clicked at row ${row}, col ${col}`);
    }
  }

  function onUp() {
    dragging = false;
    document.removeEventListener('mousemove', onMove);
    document.removeEventListener('mouseup', onUp);
  }

  document.addEventListener('mousemove', onMove);
  document.addEventListener('mouseup', onUp);

}

function updateMoveCounter() {
    const moveCounterElement = document.getElementById('moveCounter');
    moveCounterElement.textContent = `Moves: ${moveCount}`;
  }
  




function trySlide(idx, direction) {
    const size = 4;
    const emptyIndex = tiles.indexOf(null);
    const clickedRow = Math.floor(idx / size);
    const clickedCol = idx % size;
    const emptyRow = Math.floor(emptyIndex / size);
    const emptyCol = emptyIndex % size;
  
    let targetIdx = -1;
  
    if (direction === 'left' && clickedCol > 0 && emptyIndex === idx - 1) {
      targetIdx = idx - 1;
    }
    else if (direction === 'right' && clickedCol < size - 1 && emptyIndex === idx + 1) {
      targetIdx = idx + 1;
    }
    else if (direction === 'up' && clickedRow > 0 && emptyIndex === idx - size) {
      targetIdx = idx - size;
    }
    else if (direction === 'down' && clickedRow < size - 1 && emptyIndex === idx + size) {
      targetIdx = idx + size;
    }
  
    if (targetIdx !== -1) {
      [tiles[idx], tiles[emptyIndex]] = [tiles[emptyIndex], tiles[idx]];
      moveCount++;
        updateMoveCounter();
      buildBoard();
      checkIfSolved();
    }
  }
  






      
  };
  
window.onload = function() {
    const selector = document.getElementById('backgroundSelector');
    const preview = document.getElementById('backgroundPreview');
    
    let timerInterval = null;
    let timeElapsed = 0;
    let selectedBackgroundUrl = '';
    let tiles = []; 
    let moveCount = 0;
    let gameActive = false;

    const gameBoard = document.getElementById('gameBoard'); 
    
    // Initialize RBAC system immediately
    function initializeRBACSystem() {
        if (typeof RBAC !== 'undefined') {
            // Get session data from PHP (this should be inline in the HTML)
            const phpSessionData = window.phpSessionData || {};
            const userRole = phpSessionData.role || 'guest';
            const userName = phpSessionData.player || '';
            
            console.log('🔧 Initializing RBAC system...');
            console.log('📊 PHP Session Data:', phpSessionData);
            console.log('👤 User Role:', userRole);
            console.log('📝 User Name:', userName);
            
            RBAC.init(userRole, userName);
            RBAC.applyRoleBasedUI();
            
            console.log('✅ RBAC initialized successfully');
            console.log('🎯 Current user info:', RBAC.getCurrentUser());
            console.log('🔑 Has play_game permission:', RBAC.hasPermission('play_game'));
            
            return true;
        } else {
            console.warn('❌ RBAC system not available');
            return false;
        }
    }
    
    // Try to initialize RBAC immediately
    let rbacInitialized = initializeRBACSystem();
    
    // If RBAC wasn't ready, try again after a short delay
    if (!rbacInitialized) {
        setTimeout(() => {
            rbacInitialized = initializeRBACSystem();
        }, 100);
    }
    
    // Handle background image selection
    if (selector) {
        selector.addEventListener('change', function() {
            const selectedImage = this.value;
            if (selectedImage) {
                selectedBackgroundUrl = selectedImage;
                preview.src = selectedImage;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
    }
  
    window.startGame = function(event) {
        event.preventDefault();
        const submittedButton = event.submitter;
        
        console.log('🎮 startGame called');
        console.log('submittedButton:', submittedButton);
        console.log('submittedButton.value:', submittedButton ? submittedButton.value : 'undefined');
        
        if (submittedButton && submittedButton.value === "start") {
            console.log('✅ Start button confirmed');
            
            // Ensure RBAC is initialized
            if (!rbacInitialized) {
                console.log('⚠️ RBAC not initialized, attempting to initialize...');
                rbacInitialized = initializeRBACSystem();
            }
            
            // Check if RBAC is available
            if (typeof RBAC !== 'undefined') {
                console.log('✅ RBAC is available');
                
                // Double-check RBAC initialization with current session data
                const sessionData = window.phpSessionData || {};
                if (sessionData.role && sessionData.role !== 'guest') {
                    console.log('🔧 Re-initializing RBAC with session data:', sessionData);
                    RBAC.init(sessionData.role, sessionData.player || '');
                }
                
                // Get current user info
                const currentUser = RBAC.getCurrentUser();
                console.log('👤 Current user info:', currentUser);
                console.log('🔑 Current user role:', currentUser.role);
                console.log('📝 User permissions:', currentUser.permissions);
                
                // Test permission directly
                const hasPlayPermission = RBAC.hasPermission('play_game');
                console.log('🎯 Has play_game permission:', hasPlayPermission);
                
                if (hasPlayPermission) {
                    console.log('✅ Permission check passed - starting game');
                    startGameLogic();
                } else {
                    console.log('❌ Permission check failed');
                    console.log('🔍 Debug info:');
                    console.log('  - Current role in RBAC:', currentUser.role);
                    console.log('  - Session data role:', sessionData.role);
                    console.log('  - Available permissions:', currentUser.permissions);
                    
                    console.log('Available roles with play_game permission:');
                    
                    // Check which roles have play_game permission
                    for (const [role, permissions] of Object.entries(RBAC.roles)) {
                        if (permissions.includes('play_game')) {
                            console.log(`  - ${role}: HAS play_game permission`);
                        } else {
                            console.log(`  - ${role}: does NOT have play_game permission`);
                        }
                    }
                    
                    // Use the RBAC error handling
                    RBAC.executeWithPermission('play_game', function() {
                        console.log('✅ This should not appear if permission failed');
                        startGameLogic();
                    }, "You need to be logged in as a player or admin to start the game");
                }
            } else {
                console.log('❌ RBAC not available, starting game directly...');
                startGameLogic();
            }
        } else {
            console.log('❌ Not a start game submission or button not found');
        }
    };
    
    function startGameLogic() {
        selectedBackgroundUrl = selector.value;
        if (!selectedBackgroundUrl) {
            alert("Please select a background image first.");
            return;
        }

        initTiles();
        shuffleTiles();
        buildBoard();
        
        // Reset & start timer
        moveCount = 0;
        timeElapsed = 0;
        gameActive = true;
        updateTimerDisplay();
        updateMoveCounter();
        
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            if (gameActive) {
                timeElapsed++;
                updateTimerDisplay();
            }
        }, 1000);
        
        // Show game message
        const gameMessage = document.getElementById('gameMessage');
        if (gameMessage) {
            gameMessage.textContent = "Game started! Click and drag tiles to move them.";
        }
    }
      
    function initTiles() {
        tiles = [];
        for (let i = 1; i <= 15; i++) {
          tiles.push(i);
        }
        tiles.push(null); // last tile is empty
    }
      


      function updateTimerDisplay() {
        const timerElement = document.getElementById('timer');
        if (timerElement) {
          timerElement.textContent = `${timeElapsed}s`;
          // Add a subtle pulse effect for each second
          timerElement.style.transform = 'scale(1.05)';
          setTimeout(() => {
            timerElement.style.transform = 'scale(1)';
          }, 100);
        }
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
      
        // backgroundSize 400% means we need to position at 0%, 33.333%, 66.666%, 100%
        const positions = [0, 33.333, 66.666, 100];
        return `${positions[col]}% ${positions[row]}%`;
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
          gameActive = false;
          clearInterval(timerInterval); // stop timer
          
          // Add completion animation
          gameBoard.classList.add('game-completed');
          setTimeout(() => {
            gameBoard.classList.remove('game-completed');
          }, 2000);
          
          const gameMessage = document.getElementById('gameMessage');
          if (gameMessage) {
            gameMessage.innerHTML = `🎉 <strong>Congratulations!</strong> You solved the puzzle in ${timeElapsed} seconds with ${moveCount} moves!`;
          }
          
          // You could add game session saving here
          setTimeout(() => {
            alert(`🎉 You solved the puzzle in ${timeElapsed} seconds with ${moveCount} moves!`);
          }, 100);
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
          // Add visual feedback for successful move
          const tiles_elements = gameBoard.children;
          if (tiles_elements[idx]) {
            tiles_elements[idx].classList.add('moved');
            setTimeout(() => {
              if (tiles_elements[idx]) {
                tiles_elements[idx].classList.remove('moved');
              }
            }, 300);
          }
          
          // swap tiles
          [tiles[idx], tiles[emptyIndex]] = [tiles[emptyIndex], tiles[idx]];
          moveCount++;
          updateMoveCounter();
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
  
  // Add visual feedback
  const tile = e.target;
  tile.classList.add('dragging');

  function onMove(ev) {
    if (!dragging) return;
    const dx = ev.clientX - startX;
    const dy = ev.clientY - startY;

    const threshold = 30; // px to trigger move

    if (Math.abs(dx) > threshold || Math.abs(dy) > threshold) {
      dragging = false;
      tile.classList.remove('dragging');
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
    }
  }

  function onUp() {
    dragging = false;
    tile.classList.remove('dragging');
    document.removeEventListener('mousemove', onMove);
    document.removeEventListener('mouseup', onUp);
  }

  document.addEventListener('mousemove', onMove);
  document.addEventListener('mouseup', onUp);
}

function updateMoveCounter() {
    const moveCounterElement = document.getElementById('moveCounter');
    if (moveCounterElement) {
      moveCounterElement.textContent = `${moveCount}`;
      // Add a subtle bounce effect for each move
      moveCounterElement.style.transform = 'scale(1.1)';
      setTimeout(() => {
        moveCounterElement.style.transform = 'scale(1)';
      }, 150);
    }
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
      // Add visual feedback for successful move
      const tiles_elements = gameBoard.children;
      if (tiles_elements[idx]) {
        tiles_elements[idx].classList.add('moved');
        setTimeout(() => {
          if (tiles_elements[idx]) {
            tiles_elements[idx].classList.remove('moved');
          }
        }, 300);
      }
      
      [tiles[idx], tiles[emptyIndex]] = [tiles[emptyIndex], tiles[idx]];
      moveCount++;
      updateMoveCounter();
      buildBoard();
      checkIfSolved();
    }
  }
  






      
  };
  
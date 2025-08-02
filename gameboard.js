/**
 * Fifteen Puzzle Game - Improved version with better game logic
 * Everyone can play, only RBAC restriction is admin panel access
 */

window.onload = function() {
    console.log('🎮 Game loaded - Improved version');
    
    // Initialize RBAC with session data
    if (typeof RBAC !== 'undefined' && window.phpSessionData) {
        const userRole = window.phpSessionData.role;
        const userName = window.phpSessionData.player;
        
        console.log('🔧 Initializing RBAC:', { role: userRole, username: userName });
        RBAC.init(userRole, userName);
    }
    
    // Game variables
    const selector = document.getElementById('backgroundSelector');
    const preview = document.getElementById('backgroundPreview');
    let timerInterval = null;
    let timeElapsed = 0;
    let selectedBackgroundUrl = '';
    let gameBoard = document.getElementById('gameBoard');
    let tiles = []; 
    let moveCount = 0;
    let gameActive = false;
    
    // Puzzle state: 0-14 are tile numbers, 15 is empty space
    let puzzleState = [];

    // Background image selection
    if (selector && preview) {
        selector.addEventListener('change', function() {
            const selectedPath = this.value;
            if (selectedPath) {
                preview.src = selectedPath;
                preview.style.display = 'block';
                selectedBackgroundUrl = selectedPath;
            } else {
                preview.style.display = 'none';
                selectedBackgroundUrl = '';
            }
        });
    }

    // Start game function (no permission checks - everyone can play)
    window.startGame = function(event) {
        if (event) {
            event.preventDefault();
        }
        
        console.log('🎮 Starting game...');
        
        if (!selectedBackgroundUrl) {
            alert('Please select a background image first!');
            return false;
        }
        
        // Start the actual game
        initializeGame();
        return false;
    };
    
    // Add restart game function
    window.restartGame = function() {
        if (gameActive || confirm('Are you sure you want to restart the game?')) {
            initializeGame();
        }
    };

    function initializeGame() {
        console.log('🎯 Initializing game with image:', selectedBackgroundUrl);
        
        // Reset game state
        gameActive = true;
        moveCount = 0;
        timeElapsed = 0;
        
        // Clear any existing timer
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        
        // Update UI
        document.getElementById('moveCounter').textContent = moveCount;
        document.getElementById('timer').textContent = '0s';
        document.getElementById('gameMessage').textContent = 'Game started! Click tiles adjacent to the empty space to move them.';
        document.getElementById('gameMessage').style.color = '#2c3e50';
        
        // Show game controls
        if (typeof showGameControls !== 'undefined') {
            showGameControls();
        }
        
        // Create the puzzle
        createPuzzleTiles();
        shufflePuzzle();
        startTimer();
    }

    function createPuzzleTiles() {
        gameBoard.innerHTML = '';
        tiles = [];
        
        // Initialize puzzle state (0-14 are tiles, 15 is empty)
        puzzleState = Array.from({length: 16}, (_, i) => i);
        
        // Create tiles
        for (let position = 0; position < 16; position++) {
            const tile = document.createElement('div');
            tile.className = 'tile';
            tile.dataset.position = position;
            
            updateTileContent(tile, position);
            gameBoard.appendChild(tile);
            tiles.push(tile);
        }
    }
    
    function updateTileContent(tile, position) {
        const tileNumber = puzzleState[position];
        
        if (tileNumber === 15) {
            // Empty tile
            tile.classList.add('empty');
            tile.classList.remove('game-tile');
            tile.style.backgroundImage = '';
            tile.innerHTML = '';
            tile.style.cursor = 'default';
            tile.onclick = null;
        } else {
            // Regular tile
            tile.classList.remove('empty');
            tile.classList.add('game-tile');
            tile.style.backgroundImage = `url('${selectedBackgroundUrl}')`;
            tile.style.cursor = 'pointer';
            
            // Calculate background position for this tile
            const row = Math.floor(tileNumber / 4);
            const col = tileNumber % 4;
            const bgX = -(col * 25); // 25% per column (100% / 4 columns)
            const bgY = -(row * 25); // 25% per row (100% / 4 rows)
            
            tile.style.backgroundPosition = `${bgX}% ${bgY}%`;
            tile.style.backgroundSize = '400% 400%';
            
            // Add tile number for clarity
            tile.innerHTML = `<div class="tile-number">${tileNumber + 1}</div>`;
            
            // Add click handler
            tile.onclick = () => handleTileClick(position);
            
            // Add drag handlers
            tile.draggable = true;
            tile.addEventListener('dragstart', (e) => handleDragStart(e, position));
            tile.addEventListener('dragover', handleDragOver);
            tile.addEventListener('drop', (e) => handleDrop(e, position));
        }
    }

    function shufflePuzzle() {
        // Start with solved state
        puzzleState = Array.from({length: 16}, (_, i) => i);
        
        // Perform 1000 random valid moves to ensure solvability
        for (let i = 0; i < 1000; i++) {
            const emptyPos = puzzleState.indexOf(15);
            const adjacentPositions = getAdjacentPositions(emptyPos);
            
            if (adjacentPositions.length > 0) {
                const randomPos = adjacentPositions[Math.floor(Math.random() * adjacentPositions.length)];
                // Swap empty space with adjacent tile
                [puzzleState[emptyPos], puzzleState[randomPos]] = [puzzleState[randomPos], puzzleState[emptyPos]];
            }
        }
        
        // Update all tiles to reflect the shuffled state
        for (let position = 0; position < 16; position++) {
            updateTileContent(tiles[position], position);
        }
        
        console.log('🔀 Puzzle shuffled:', puzzleState);
    }

    function getAdjacentPositions(pos) {
        const row = Math.floor(pos / 4);
        const col = pos % 4;
        const adjacent = [];
        
        // Check all four directions
        if (row > 0) adjacent.push((row - 1) * 4 + col); // up
        if (row < 3) adjacent.push((row + 1) * 4 + col); // down
        if (col > 0) adjacent.push(row * 4 + (col - 1)); // left
        if (col < 3) adjacent.push(row * 4 + (col + 1)); // right
        
        return adjacent;
    }

    function handleTileClick(position) {
        if (!gameActive) return;
        
        const emptyPos = puzzleState.indexOf(15);
        const adjacentPositions = getAdjacentPositions(emptyPos);
        
        if (adjacentPositions.includes(position)) {
            // Perform the move
            [puzzleState[position], puzzleState[emptyPos]] = [puzzleState[emptyPos], puzzleState[position]];
            
            // Update the visual tiles
            updateTileContent(tiles[position], position);
            updateTileContent(tiles[emptyPos], emptyPos);
            
            moveCount++;
            document.getElementById('moveCounter').textContent = moveCount;
            
            // Add animation
            tiles[position].classList.add('moved');
            setTimeout(() => {
                tiles[position].classList.remove('moved');
            }, 300);
            
            // Check if puzzle is solved
            if (isPuzzleSolved()) {
                handleGameWin();
            } else {
                // Debug: show current state
                console.log('🎯 Current puzzle state:', puzzleState.map(n => n === 15 ? '  ' : (n + 1).toString().padStart(2)).join(' '));
            }
        } else {
            // Give user feedback for invalid move
            tiles[position].classList.add('invalid-move');
            setTimeout(() => {
                tiles[position].classList.remove('invalid-move');
            }, 300);
        }
    }

    function isPuzzleSolved() {
        // Check if tiles are in correct order (0-14, with empty at position 15)
        for (let i = 0; i < 16; i++) {
            if (puzzleState[i] !== i) {
                return false;
            }
        }
        return true;
    }

    function handleGameWin() {
        gameActive = false;
        clearInterval(timerInterval);
        
        // Add win animation
        gameBoard.classList.add('game-completed');
        
        // Calculate performance metrics
        const minutes = Math.floor(timeElapsed / 60);
        const seconds = timeElapsed % 60;
        const timeStr = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
        
        // Performance rating
        let rating = '';
        if (moveCount <= 100) rating = '🏆 Excellent!';
        else if (moveCount <= 200) rating = '🥈 Great job!';
        else if (moveCount <= 300) rating = '🥉 Well done!';
        else rating = '👍 Good effort!';
        
        // Update message with celebration and options
        document.getElementById('gameMessage').innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 2em; margin-bottom: 10px;">🎉 PUZZLE SOLVED! 🎉</div>
                <div style="font-size: 1.2em; margin-bottom: 15px;">${rating}</div>
                <div style="margin-bottom: 15px;">
                    <strong>Your Stats:</strong><br>
                    ⏱️ Time: <strong>${timeStr}</strong><br>
                    🎯 Moves: <strong>${moveCount}</strong>
                </div>
                <button onclick="restartGame()" class="btn btn-primary" style="margin: 5px;">🔄 Play Again</button>
                <button onclick="location.reload()" class="btn btn-secondary" style="margin: 5px;">🖼️ Choose New Image</button>
            </div>
        `;
        document.getElementById('gameMessage').style.color = '#27ae60';
        
        // Hide game controls
        if (typeof hideGameControls !== 'undefined') {
            hideGameControls();
        }
        
        // Remove animation after 3 seconds
        setTimeout(() => {
            gameBoard.classList.remove('game-completed');
        }, 3000);
        
        console.log(`🏁 Game completed! Moves: ${moveCount}, Time: ${timeStr}`);
    }

    function startTimer() {
        if (timerInterval) clearInterval(timerInterval);
        
        timeElapsed = 0;
        timerInterval = setInterval(() => {
            timeElapsed++;
            const minutes = Math.floor(timeElapsed / 60);
            const seconds = timeElapsed % 60;
            const timeStr = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
            document.getElementById('timer').textContent = timeStr;
        }, 1000);
    }

    // Drag and drop handlers
    let draggedPosition = null;
    
    function handleDragStart(e, position) {
        if (!gameActive) {
            e.preventDefault();
            return;
        }
        draggedPosition = position;
        e.target.classList.add('dragging');
    }
    
    function handleDragOver(e) {
        e.preventDefault();
    }
    
    function handleDrop(e, position) {
        e.preventDefault();
        if (draggedPosition === null || !gameActive) return;
        
        const draggedTile = tiles[draggedPosition];
        draggedTile.classList.remove('dragging');
        
        // Only allow dropping on empty tile
        if (puzzleState[position] === 15) {
            const emptyPos = position;
            const adjacentPositions = getAdjacentPositions(emptyPos);
            
            if (adjacentPositions.includes(draggedPosition)) {
                // Perform the move
                [puzzleState[draggedPosition], puzzleState[emptyPos]] = [puzzleState[emptyPos], puzzleState[draggedPosition]];
                
                // Update the visual tiles
                updateTileContent(tiles[draggedPosition], draggedPosition);
                updateTileContent(tiles[emptyPos], emptyPos);
                
                moveCount++;
                document.getElementById('moveCounter').textContent = moveCount;
                
                if (isPuzzleSolved()) {
                    handleGameWin();
                }
            }
        }
        
        draggedPosition = null;
    }
};

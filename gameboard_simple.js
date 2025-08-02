/**
 * Fifteen Puzzle Game - Simplified version
 * Everyone can play, only RBAC restriction is admin panel access
 */

window.onload = function() {
    console.log('🎮 Game loaded - Simplified version');
    
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
    let tiles = []; 
    let moveCount = 0;
    let gameActive = false;
    const gameBoard = document.getElementById('gameBoard'); 

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

    function initializeGame() {
        console.log('🎯 Initializing game with image:', selectedBackgroundUrl);
        
        gameActive = true;
        moveCount = 0;
        timeElapsed = 0;
        
        // Update UI
        document.getElementById('moveCounter').textContent = moveCount;
        document.getElementById('timer').textContent = '0s';
        
        // Create the puzzle
        createPuzzleTiles();
        shuffleTiles();
        startTimer();
        
        // Update game message
        document.getElementById('gameMessage').textContent = 'Game started! Arrange the tiles to complete the puzzle.';
        document.getElementById('gameMessage').style.color = '#2c3e50';
    }

    function createPuzzleTiles() {
        gameBoard.innerHTML = '';
        tiles = [];
        
        // Create 15 tiles (positions 0-14) and 1 empty space (position 15)
        for (let i = 0; i < 16; i++) {
            const tile = document.createElement('div');
            tile.className = 'tile';
            
            if (i === 15) {
                // Empty tile
                tile.classList.add('empty');
                tile.dataset.position = i;
            } else {
                // Regular tile
                tile.dataset.position = i;
                tile.style.backgroundImage = `url('${selectedBackgroundUrl}')`;
                
                // Calculate background position for this piece
                const row = Math.floor(i / 4);
                const col = i % 4;
                const bgX = -(col * 25); // 25% per column (100% / 4 columns)
                const bgY = -(row * 25); // 25% per row (100% / 4 rows)
                
                tile.style.backgroundPosition = `${bgX}% ${bgY}%`;
                tile.style.backgroundSize = '400% 400%';
                
                // Add click handler
                tile.addEventListener('click', () => handleTileClick(i));
                
                // Add drag handlers
                tile.draggable = true;
                tile.addEventListener('dragstart', handleDragStart);
                tile.addEventListener('dragover', handleDragOver);
                tile.addEventListener('drop', handleDrop);
            }
            
            gameBoard.appendChild(tile);
            tiles.push(tile);
        }
    }

    function shuffleTiles() {
        // Simple shuffle: swap tiles randomly 1000 times
        for (let i = 0; i < 1000; i++) {
            const emptyPos = findEmptyPosition();
            const adjacentPositions = getAdjacentPositions(emptyPos);
            
            if (adjacentPositions.length > 0) {
                const randomPos = adjacentPositions[Math.floor(Math.random() * adjacentPositions.length)];
                swapTiles(emptyPos, randomPos);
            }
        }
    }

    function findEmptyPosition() {
        for (let i = 0; i < tiles.length; i++) {
            if (tiles[i].classList.contains('empty')) {
                return i;
            }
        }
        return 15; // fallback
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
        
        const emptyPos = findEmptyPosition();
        const adjacentPositions = getAdjacentPositions(emptyPos);
        
        if (adjacentPositions.includes(position)) {
            swapTiles(position, emptyPos);
            moveCount++;
            document.getElementById('moveCounter').textContent = moveCount;
            
            // Add animation
            tiles[emptyPos].classList.add('moved');
            setTimeout(() => {
                tiles[emptyPos].classList.remove('moved');
            }, 300);
            
            // Check if puzzle is solved
            if (isPuzzleSolved()) {
                handleGameWin();
            }
        }
    }

    function swapTiles(pos1, pos2) {
        // Swap the actual DOM elements
        const tile1 = tiles[pos1];
        const tile2 = tiles[pos2];
        
        // Create temporary markers
        const temp1 = document.createElement('div');
        const temp2 = document.createElement('div');
        
        // Insert markers
        gameBoard.insertBefore(temp1, tile1);
        gameBoard.insertBefore(temp2, tile2);
        
        // Swap positions
        gameBoard.insertBefore(tile1, temp2);
        gameBoard.insertBefore(tile2, temp1);
        
        // Remove markers
        gameBoard.removeChild(temp1);
        gameBoard.removeChild(temp2);
        
        // Update tiles array
        tiles[pos1] = tile2;
        tiles[pos2] = tile1;
    }

    function isPuzzleSolved() {
        // Check if tiles are in correct order (0-14, with empty at position 15)
        for (let i = 0; i < 15; i++) {
            const expectedPosition = parseInt(tiles[i].dataset.position);
            if (i !== expectedPosition) {
                return false;
            }
        }
        return tiles[15].classList.contains('empty');
    }

    function handleGameWin() {
        gameActive = false;
        clearInterval(timerInterval);
        
        // Add win animation
        gameBoard.classList.add('game-completed');
        
        // Update message
        const minutes = Math.floor(timeElapsed / 60);
        const seconds = timeElapsed % 60;
        const timeStr = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
        
        document.getElementById('gameMessage').innerHTML = `
            🎉 <strong>Congratulations!</strong> 🎉<br>
            You solved the puzzle in <strong>${moveCount}</strong> moves and <strong>${timeStr}</strong>!
        `;
        document.getElementById('gameMessage').style.color = '#27ae60';
        
        // Remove animation after 2 seconds
        setTimeout(() => {
            gameBoard.classList.remove('game-completed');
        }, 2000);
    }

    function startTimer() {
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
    let draggedTile = null;
    
    function handleDragStart(e) {
        if (!gameActive) {
            e.preventDefault();
            return;
        }
        draggedTile = this;
        this.classList.add('dragging');
    }
    
    function handleDragOver(e) {
        e.preventDefault();
    }
    
    function handleDrop(e) {
        e.preventDefault();
        if (!draggedTile || !gameActive) return;
        
        const droppedOn = this;
        draggedTile.classList.remove('dragging');
        
        // Only allow dropping on empty tile
        if (droppedOn.classList.contains('empty')) {
            const draggedPos = Array.from(tiles).indexOf(draggedTile);
            const droppedPos = Array.from(tiles).indexOf(droppedOn);
            const emptyPos = findEmptyPosition();
            
            // Check if dragged tile is adjacent to empty space
            const adjacentPositions = getAdjacentPositions(emptyPos);
            if (adjacentPositions.includes(draggedPos)) {
                swapTiles(draggedPos, emptyPos);
                moveCount++;
                document.getElementById('moveCounter').textContent = moveCount;
                
                if (isPuzzleSolved()) {
                    handleGameWin();
                }
            }
        }
        
        draggedTile = null;
    }
};

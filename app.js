const MAX_ATTEMPTS = 6;
let currentAttempt = 0;
let currentLetterIdx = 1;
let currentGuess = ["", "", "", "", ""];

const gridElement = document.getElementById('grid');
const messageElement = document.getElementById('message');
const restartBtn = document.getElementById('btn-restart');

gridElement.style.gridTemplateRows = `repeat(${MAX_ATTEMPTS}, 1fr)`;
gridElement.style.gridTemplateColumns = `repeat(${WORD_LENGTH}, 1fr)`;

function initGrid() {
    gridElement.innerHTML = ""; 
    
    currentAttempt = 0;
    currentLetterIdx = 1;
    currentGuess = Array(WORD_LENGTH).fill("");

    for (let row = 0; row < MAX_ATTEMPTS; row++) {
        for (let col = 0; col < WORD_LENGTH; col++) {
            const cell = document.createElement('div');
            cell.classList.add('cell');
            cell.setAttribute('id', `cell-${row}-${col}`);
            
            if (col === 0) {
                cell.innerText = FIRST_LETTER;
            } else {
                cell.innerText = ".";
            }
            gridElement.appendChild(cell);
        }
    }
    currentGuess[0] = FIRST_LETTER;
    restartBtn.style.display = 'none'; 
    messageElement.innerText = "";
}

document.addEventListener('keydown', handleKeyPress);

function handleKeyPress(e) {
    const key = e.key.toUpperCase();

    if (currentAttempt >= MAX_ATTEMPTS) return;

    if (key === 'ENTER') {
        if (currentLetterIdx === WORD_LENGTH) {
            checkWord();
        } else {
            messageElement.innerText = "Le mot n'est pas complet !";
        }
    }

    if (key === 'BACKSPACE') {
        if (currentLetterIdx > 1) {
            currentLetterIdx--;
            currentGuess[currentLetterIdx] = "";
            const cell = document.getElementById(`cell-${currentAttempt}-${currentLetterIdx}`);
            cell.innerText = ".";
            messageElement.innerText = "";
        }
    }

    if (/^[A-Z]$/.test(key)) {
        if (currentLetterIdx < WORD_LENGTH) {
            const cell = document.getElementById(`cell-${currentAttempt}-${currentLetterIdx}`);
            cell.innerText = key;
            currentGuess[currentLetterIdx] = key;
            currentLetterIdx++;
            messageElement.innerText = "";
        }
    }
}

function checkWord() {
    const guessStr = currentGuess.join('');
    const secretLetterCounts = {};

    for (let char of SECRET_WORD) {
        secretLetterCounts[char] = (secretLetterCounts[char] || 0) + 1;
    }

    const states = Array(WORD_LENGTH).fill('absent');

    for (let i = 0; i < WORD_LENGTH; i++) {
        if (guessStr[i] === SECRET_WORD[i]) {
            states[i] = 'correct';
            secretLetterCounts[guessStr[i]]--;
        }
    }

    for (let i = 0; i < WORD_LENGTH; i++) {
        if (states[i] !== 'correct') {
            if (secretLetterCounts[guessStr[i]] > 0) {
                states[i] = 'present';
                secretLetterCounts[guessStr[i]]--;
            }
        }
    }

    for (let i = 0; i < WORD_LENGTH; i++) {
        const cell = document.getElementById(`cell-${currentAttempt}-${i}`);
        cell.className = 'cell ' + states[i];
    }

    if (guessStr === SECRET_WORD) {
        messageElement.style.color = "#2ecc71";
        messageElement.innerText = "Félicitations ! Vous avez trouvé le mot ! 🎉";
        const finalAttempts = currentAttempt + 1; // Nombre réel de lignes jouées
        currentAttempt = MAX_ATTEMPTS; 
        endGame(1, finalAttempts); // Victoire !
        return;
    }

    currentAttempt++;

    if (currentAttempt < MAX_ATTEMPTS) {
        currentLetterIdx = 1;
        currentGuess = Array(WORD_LENGTH).fill("");
        currentGuess[0] = FIRST_LETTER;
        
        const nextRowFirstCell = document.getElementById(`cell-${currentAttempt}-0`);
        nextRowFirstCell.innerText = FIRST_LETTER;
    } else {
        messageElement.style.color = "#ff9f9f";
        messageElement.innerText = `Dommage ! Le mot secret était : ${SECRET_WORD}`;
        endGame(0, 6); // Défaite au bout de 6 essais
    }
}

// Envoi asynchrone des données de score à l'API PHP
async function sendScoreToServer(won, attempts) {
    try {
        await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'save_score', won: won, attempts: attempts })
        });
    } catch (error) {
        console.error("Impossible de joindre l'API pour sauvegarder le score :", error);
    }
}

function endGame(won, attempts) {
    restartBtn.style.display = 'block';
    sendScoreToServer(won, attempts); // Déclenchement automatique de la sauvegarde
}

restartBtn.addEventListener('click', () => {
    window.location.reload();
});

initGrid();
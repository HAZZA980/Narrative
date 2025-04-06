<script>
    document.addEventListener('DOMContentLoaded', () => {
        initializeQuizForm();
    });

    function initializeQuizForm() {
        const existingQuestions = <?php echo json_encode($questions); ?>;
        existingQuestions.forEach((questionData) => {
            if (questionData && questionData.question && questionData.answers.length > 0) {
                addExistingQuestion(questionData);
            }
        });
    }

    function addExistingQuestion(questionData) {
        const quizContainer = document.getElementById('quizContainer');
        const questionBlock = document.createElement('div');
        questionBlock.className = 'question-block';
        questionBlock.setAttribute('draggable', 'true');

        const questionId = questionData.question['id']; // Use real ID from DB

        const dragHandle = document.createElement('div');
        dragHandle.className = 'drag-handle';
        dragHandle.innerHTML = '☰';
        questionBlock.appendChild(dragHandle);
        addDragEvents(questionBlock);

        const questionNumber = document.createElement('div');
        questionNumber.className = 'question-number';
        questionNumber.textContent = `Question ${quizContainer.children.length + 1}`;
        questionBlock.appendChild(questionNumber);

        const removeBtn = document.createElement('button');
        removeBtn.className = 'btn-remove';
        removeBtn.textContent = '✖';
        removeBtn.onclick = () => removeQuestion(questionBlock);
        questionBlock.appendChild(removeBtn);

        const questionInput = document.createElement('input');
        questionInput.type = 'text';
        questionInput.name = 'question[]';
        questionInput.className = 'form-input';
        questionInput.placeholder = 'Enter question';
        questionInput.value = questionData.question['question_text'];
        questionBlock.appendChild(questionInput);

        const questionIdInput = document.createElement('input');
        questionIdInput.type = 'hidden';
        questionIdInput.name = 'questionId[]';
        questionIdInput.value = questionId;
        questionBlock.appendChild(questionIdInput);

        const answersRow = document.createElement('div');
        answersRow.className = 'answers-row';

        for (let i = 0; i < 4; i++) {
            const answerInput = document.createElement('input');
            answerInput.type = 'text';
            answerInput.name = `answers[${questionId}][]`; // 🛠️ Use question ID as key
            answerInput.className = 'form-input';
            answerInput.placeholder = `Answer ${i + 1}`;
            answerInput.value = questionData.answers[i] ? questionData.answers[i]['answer_text'] : '';
            answersRow.appendChild(answerInput);

            if (questionData.answers[i]) {
                const answerIdInput = document.createElement('input');
                answerIdInput.type = 'hidden';
                answerIdInput.name = `answerId[${questionId}][]`; // 🛠️ Match ID-based grouping
                answerIdInput.value = questionData.answers[i]['id'];
                answersRow.appendChild(answerIdInput);
            }
        }

        questionBlock.appendChild(answersRow);
        quizContainer.appendChild(questionBlock);
        updateRemoveButtons();
    }

    function addQuestion() {
        const quizContainer = document.getElementById('quizContainer');
        const questionBlock = document.createElement('div');
        questionBlock.className = 'question-block';
        questionBlock.setAttribute('draggable', 'true');

        const newQuestionId = 'new_' + Date.now(); // 🛠️ Temporary ID for new questions

        const dragHandle = document.createElement('div');
        dragHandle.className = 'drag-handle';
        dragHandle.innerHTML = '☰';
        questionBlock.appendChild(dragHandle);
        addDragEvents(questionBlock);

        const questionNumber = document.createElement('div');
        questionNumber.className = 'question-number';
        questionNumber.textContent = `Question ${quizContainer.children.length + 1}`;
        questionBlock.appendChild(questionNumber);

        const removeBtn = document.createElement('button');
        removeBtn.className = 'btn-remove';
        removeBtn.textContent = '✖';
        removeBtn.onclick = () => removeQuestion(questionBlock);
        questionBlock.appendChild(removeBtn);

        const questionInput = document.createElement('input');
        questionInput.type = 'text';
        questionInput.name = 'question[]';
        questionInput.className = 'form-input';
        questionInput.placeholder = 'Enter question';
        questionInput.required = true;
        questionBlock.appendChild(questionInput);

        const questionIdInput = document.createElement('input');
        questionIdInput.type = 'hidden';
        questionIdInput.name = 'questionId[]';
        questionIdInput.value = newQuestionId; // 🛠️ Use temporary ID
        questionBlock.appendChild(questionIdInput);

        const answersRow = document.createElement('div');
        answersRow.className = 'answers-row';

        for (let i = 0; i < 4; i++) {
            const answerInput = document.createElement('input');
            answerInput.type = 'text';
            answerInput.name = `answers[${newQuestionId}][]`; // 🛠️ Match temp ID
            answerInput.className = 'form-input';
            answerInput.placeholder = `Answer ${i + 1}`;
            answersRow.appendChild(answerInput);
        }

        questionBlock.appendChild(answersRow);
        quizContainer.appendChild(questionBlock);
        updateRemoveButtons();
    }

    function removeQuestion(block) {
        const quizContainer = document.getElementById('quizContainer');
        if (quizContainer.children.length > 1) {
            quizContainer.removeChild(block);
            updateRemoveButtons();
            updateQuestionNumbers();
        }
    }

    function updateQuestionNumbers() {
        const blocks = document.querySelectorAll('.question-block');
        blocks.forEach((block, idx) => {
            const numberDiv = block.querySelector('.question-number');
            if (numberDiv) {
                numberDiv.textContent = `Question ${idx + 1}`;
            }
        });
    }

    function updateRemoveButtons() {
        const blocks = document.querySelectorAll('.question-block');
        blocks.forEach((block) => {
            const btn = block.querySelector('.btn-remove');
            btn.style.display = blocks.length > 1 ? 'block' : 'none';
        });
    }

    let draggedBlock = null;

    function addDragEvents(block) {
        block.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', '');
            block.classList.add('dragging');
            draggedBlock = block;
        });

        block.addEventListener('dragend', () => {
            block.classList.remove('dragging');
            draggedBlock = null;
            updateQuestionNumbers();
        });

        block.addEventListener('dragover', (e) => {
            e.preventDefault();
            block.classList.add('drag-over');
        });

        block.addEventListener('dragleave', () => {
            block.classList.remove('drag-over');
        });

        block.addEventListener('drop', (e) => {
            e.preventDefault();
            const container = document.getElementById('quizContainer');
            if (draggedBlock && draggedBlock !== block) {
                container.insertBefore(draggedBlock, block.nextSibling === draggedBlock ? block : block);
            }
            block.classList.remove('drag-over');
            updateQuestionNumbers();
        });
    }

</script>
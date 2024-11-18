@extends('layouts.app')

@section('content')
    <style>
        .dropdown-menu {
            display: none;
        }
        .dropdown.open .dropdown-menu {
            display: block;
        }


    </style>
    <div class="container mx-auto p-5">
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-cyan-500">@if(blank($surveyId)) Create Survey @else Edit Survey @endif</h2>
            <form id="surveyForm" method="POST" enctype="multipart/form-data" action="@if(blank($surveyId)) {{ route('api.save') }} @else {{ route('api.update', ['id' => $surveyId]) }} @endif">
                @csrf
                <div class="w-full">
                    <div class="w-full">
                        <div class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-300 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent rounded-lg shadow">
                            <div class="p-6 grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="xl:col-span-9 md:col-span-12">
                                    <label class="block text-sm font-medium text-gray-300">Title <span class="text-red-500">*</span></label>
                                    <input type="text"
                                        class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                        id="name"
                                        aria-describedby="name"
                                        required
                                        placeholder="Enter the title of the survey"
                                        name="name"
                                        maxlength="150"
                                        value="{{ old('name', $survey->title ?? '') }}">
                                </div>

                                <div class="xl:col-span-3 md:col-span-12">
                                    <label class="block text-sm font-medium text-gray-300">Status</label>
                                    <select class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                        id="status_select"
                                        name="status"
                                        required>
                                        <option selected value="draft">Draft</option>
                                        <option @selected(isset($survey) && $survey->status == 'published') value="published">Published</option>
                                        <option @selected(isset($survey) && $survey->status == 'completed') value="completed">Completed</option>
                                    </select>
                                </div>

                                <div class="col-span-12">
                                    <label class="block text-sm font-medium text-gray-300">Description</label>
                                    <textarea
                                        class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                        id="description"
                                        aria-describedby="description"
                                        name="description"
                                        placeholder="Enter the description of the survey"
                                        maxlength="4000000000">{{ old('description', $survey->description ?? '') }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="w-full mt-4">
                        <div class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-300 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent rounded-lg shadow">
                            <div class="flex justify-center items-center">
                                <input
                                    type="text"
                                    id="addNewSectionInput"
                                    placeholder="Section Title"
                                    class="w-72 px-4 py-2 bg-gray-700 text-gray-100 border border-gray-600 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                >
                                <button type="button" onclick="addNewSection()"
                                    class="px-6 py-2 bg-cyan-500 text-gray-900 rounded-r-lg font-bold hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                    <i class="fas fa-circle-plus"></i> Add Section
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="w-full">
                        <div id="questionsDiv"></div>
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        type="button"
                        onclick="submitForm()"
                        id="saveBtn">
                        <i class="fas fa-save mr-2"></i>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showFileName() {
            const fileInput = document.getElementById('file-input');
            const fileName = document.getElementById('file-name');
            const selectedFile = fileInput.files[0];

            if (selectedFile) {
                fileName.textContent = selectedFile.name;
            } else {
                fileName.textContent = "No file selected";
            }
        }

        let questions = @json($survey->questions ?? []);
        let sections = [];
        let surveyImg = "{{ asset($survey->cover_photo ?? '') }}";
        let lastTransactionedQuestion = null;
        @if (isset($sections) && filled($sections))
            @foreach ($sections as $section)
                sections.push({
                    id: slugText('{{ $section }}'),
                    name: '{{ $section }}',
                });
            @endforeach
            $(document).ready(function() {
                if(questions.length != 0){
                    questions.forEach(question => {
                        question.section = slugText(question.section);
                    });
                }

                setTimeout(() => {
                    renderSections();
                }, 0);
            });
        @endif


        function addNewSection() {
            let sectionName = $("#addNewSectionInput").val();
            if (sectionName == '') {
                showToast("Section name cannot be empty", 'error');
                return;
            }
            if (sections.find(s => s.name == sectionName)) {
                showToast("Section name must be unique", 'error');
                return;
            }

            if (sectionName.length > 50){
                showToast("Section name must be less than 50 characters", 'error');
                return;
            }
            let sectionId = window.crypto.randomUUID();
            let section = {
                id: sectionId,
                name: sectionName,
            }
            $("#addNewSectionInput").val('');
            sections.push(section);
            renderSections();
        }

        function addQuestion(section, type) {
            let questionId = window.crypto.randomUUID();
            if(type == 'single_choice' || type == 'multiple_choice'){
                let question = {
                    id: questionId,
                    section: section,
                    type: type,
                    question_text: '',
                    is_required: false,
                    options: [{
                            question_id: questionId,
                            text: '',
                        },
                        {
                            question_id: questionId,
                            text: '',
                        },
                    ],
                }
                questions.push(question);
            }else if(type == 'open_ended'){
                let question = {
                    id: questionId,
                    section: section,
                    type: type,
                    question_text: '',
                    is_required: false,
                }
                questions.push(question);
            }else{
                let question = {
                    id: questionId,
                    section: section,
                    type: type,
                    question_text: '',
                    is_required: false,
                    options: [{
                            question_id: questionId,
                            text: '',
                            type: 'row',
                        },
                        {
                            question_id: questionId,
                            text: '',
                            type: 'row',
                        },
                        {
                            question_id: questionId,
                            text: '',
                            type: 'column',
                        },
                        {
                            question_id: questionId,
                            text: '',
                            type: 'column',
                        },
                    ],
                }
                questions.push(question);
            }
            lastTransactionedQuestion = questionId;
            renderQuestions();
        }

        function renderSections() {
            $('#questionsDiv').html('');
            sections.forEach(section => {
                if(section.name == null || section.name == ''){
                    return;
                }
                let sectionDiv = `
                    <div class="mt-4 bg-gray-700 text-gray-300 border border-gray-600 rounded-lg shadow-md mb-4" id="sectionDiv${section.id}">
                        <div class="flex flex-col md:flex-row items-start md:items-center mt-2 px-4">
                        <div class="w-full md:w-1/2" id="section_name_${section.id}">
                            <h4 class="text-xl font-semibold mb-1">${section.name}</h4>
                        </div>
                        <div class="w-full md:w-1/2">
                            <div class="flex justify-end">
                            <div class="flex items-center">
                                <button
                                type="button"
                                class="p-2 rounded-full text-blue-600 hover:bg-blue-600 hover:text-gray-300 transition-colors duration-200"
                                onclick="editSection('${section.id}')"
                                >
                                <i class="fas fa-pen-alt"></i>
                                </button>
                                <button
                                type="button"
                                class="p-2 rounded-full text-red-600 hover:bg-red-600 hover:text-gray-300 transition-colors duration-200"
                                onclick="deleteSection('${section.id}')"
                                >
                                <i class="far fa-trash-alt"></i>
                                </button>
                            </div>
                            </div>
                        </div>
                        </div>

                        <div class="p-4" id="sectionBody${section.id}"></div>
                        <div class="p-4 border-t text-center">
                            <div class="inline-flex relative">
                                <button
                                    type="button"
                                    class="px-4 py-2 border border-yellow-500 text-yellow-600 hover:bg-yellow-600 hover:text-gray-300 rounded-l transition-colors duration-200"
                                    id="addQuestionToSectionBtn${section.id}"
                                    onclick="addQuestion('${section.id}', 'single_choice')"
                                    question-type="single_choice"
                                >
                                    <i class="fas fa-plus-circle mr-2"></i>
                                    Add Multiple Choice One Answer Question
                                </button>

                                <div class="relative inline-block text-left">
                                    <div>
                                        <button type="button"
                                                class="px-2 py-2 border border-l-0 border-yellow-500 text-yellow-600 hover:bg-yellow-600 hover:text-gray-300 rounded-r transition-colors duration-200"
                                                id="menu-button${section.id}"
                                                aria-expanded="false"
                                                aria-haspopup="true">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none hidden"
                                        id="dropdown-menu${section.id}"
                                        role="menu"
                                        aria-orientation="vertical"
                                        aria-labelledby="menu-button${section.id}"
                                        tabindex="-1">
                                        <div class="py-1" role="none">
                                            <a onclick="changeType('${section.id}', 'single_choice')"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            role="menuitem"
                                            tabindex="-1"
                                            id="${section.id}_single_choice_typeBtn"><i class="fas fa-check"></i> Multiple Choice One Answers</a>
                                            <a onclick="changeType('${section.id}', 'multiple_choice')"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            role="menuitem"
                                            tabindex="-1"
                                            id="${section.id}_multiple_choice_typeBtn">Multiple Choice Multiple Answer</a>
                                            <a onclick="changeType('${section.id}', 'open_ended')"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            role="menuitem"
                                            tabindex="-1"
                                            id="${section.id}_open_ended_typeBtn">Open Ended Question</a>
                                            <a onclick="changeType('${section.id}', 'multiple_choice_table')"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            role="menuitem"
                                            tabindex="-1"
                                            id="${section.id}_multiple_choice_table_typeBtn">Multiple Choice Table</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#questionsDiv').append(sectionDiv);

                let menuButton = document.getElementById('menu-button' + section.id);
                let dropdownMenu = document.getElementById('dropdown-menu' + section.id);

                menuButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    let isExpanded = menuButton.getAttribute('aria-expanded') === 'true';
                    toggleMenu(!isExpanded, menuButton, dropdownMenu);
                });

                document.addEventListener('click', function(e) {
                    if (!dropdownMenu.contains(e.target)) {
                        toggleMenu(false, menuButton, dropdownMenu);
                    }
                });
            });
            renderQuestions();
        }

        function toggleMenu(show, menuButton, dropdownMenu) {
            dropdownMenu.classList.toggle('hidden', !show);
            menuButton.setAttribute('aria-expanded', show);
        }

        function renderQuestions() {
            let groupedQuestions = [];
            questions.forEach(question => {
                if (groupedQuestions[question.section] == undefined) {
                    groupedQuestions[question.section] = [];
                }
                groupedQuestions[question.section].push(question);
            });
            Object.entries(groupedQuestions).forEach(([sectionId, sectionQuestions]) => {
                if(sectionId == 'null'){
                    return;
                }
                let questionsHtml = '';
                sectionQuestions.forEach((question, index) => {
                    questionsHtml += `
                        <div class="w-full mb-5 rounded-lg border shadow-sm">
                            <div class="p-4 border-b cursor-pointer" id="qText${question.id}" onclick="changeViewQuestion('${question.id}')">
                                <h6 class="text-lg font-medium">${getTypeText(question.type)}</h6>
                                <div class="flex items-center justify-end space-x-3">
                                    <div class="flex items-center">
                                        <input type="checkbox" class="w-4 h-4 mr-2 text-yellow-500 border-yellow-500 rounded"
                                            id="colorCheck${question.id}"
                                            onchange="changeReq('${question.id}', this.checked)"
                                            ${!question.is_required ? '' : 'checked'}>
                                        <label class="text-sm" for="colorCheck${question.id}">Zorunlu</label>
                                    </div>
                                    <button type="button" class="p-2 text-blue-600 rounded-full hover:bg-blue-50"
                                        onclick="copyQuestion('${question.id}', event)">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button type="button" class="p-2 text-red-600 rounded-full hover:bg-red-50"
                                        onclick="deleteQuestion('${question.id}', event)">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="p-2 text-green-600 rounded-full hover:bg-green-50"
                                        onclick="changeViewQuestion('${question.id}', event)"
                                        id="changeViewBtn${question.id}"
                                        visibility="true">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="px-4 py-2 border-b">
                                <h6 class="text-sm font-light">${index + 1}.Question)</h6>
                            </div>

                            <div class="p-4 qDiv${question.id}">
                                <textarea type="text" class="questionTextarea w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                    onchange="updateQuestionValue('${question.id}', this.value)"
                                    placeholder="Question"
                                    id="${question.id}_question">${question.question_text}</textarea>
                            </div>

                            ${question.type == 'single_choice' || question.type == 'multiple_choice' ? `
                                <div class="p-4 qDiv${question.id}">
                                    <h4 class="text-lg font-medium mb-4">Options</h4>
                                    ${question.options.map((option, optIndex) => {
                                        return `
                                        <div class="mb-4 flex items-center space-x-3">
                                            <div class="flex items-center">
                                                ${question.type == 'single_choice'
                                                    ? '<input disabled class="w-4 h-4 text-blue-600" type="radio">'
                                                    : '<input disabled class="w-4 h-4 text-blue-600" type="checkbox">'}
                                            </div>
                                            <div class="flex-1">
                                                <textarea type="text" class="choiceTextarea w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                                    id="${question.id}_option_${optIndex}"
                                                    onchange="updateOptionValue('${question.id}', '${optIndex}', this.value)"
                                                    placeholder="Option ${optIndex + 1}"
                                                    optindex="${optIndex}">${option.text}</textarea>
                                            </div>
                                            <button class="text-red-600 hover:text-red-800"
                                                onclick="deleteOption('${question.id}', '${optIndex}')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>`;
                                    }).join('')}
                                    <div class="mt-4">
                                        <button type="button"
                                            class="px-4 py-2 border border-yellow-500 text-yellow-500 rounded-md hover:bg-yellow-50"
                                            onclick="addOption('${question.id}')">
                                            <i class="fas fa-plus-circle mr-2"></i>Add Option
                                        </button>
                                    </div>
                                </div>
                            `: ''}

                            ${question.type == 'multiple_choice_table' ? `
                                <div class="p-4 grid grid-cols-1 xl:grid-cols-2 gap-6 qDiv${question.id}">
                                    <div>
                                        <h4 class="text-lg font-medium mb-4">Row</h4>
                                        <ol id="row_ol_${question.id}" class="space-y-3">
                                            ${question.options.map((option, optIndex) => {
                                                if(option.type != 'row') return '';
                                                return `
                                                <li class="flex items-center space-x-2">
                                                    <input type="text"
                                                        class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                                        placeholder="Row Content"
                                                        value="${option.text}"
                                                        onkeyup="updateOptionValue('${question.id}', '${optIndex}', this.value)">
                                                    <button class="text-red-600 hover:text-red-800"
                                                        onclick="deleteTableOption('${question.id}', '${optIndex}')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </li>`;
                                            }).join('')}
                                            <li>
                                                <button type="button"
                                                    class="px-3 py-1 border border-yellow-500 text-yellow-500 rounded-md text-sm hover:bg-yellow-50"
                                                    onclick="addTableOption('${question.id}', 'row')">
                                                    <i class="fas fa-plus-circle mr-1"></i>Add New
                                                </button>
                                            </li>
                                        </ol>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-medium mb-4">Column</h4>
                                        <ol id="column_ol_${question.id}" class="space-y-3">
                                            ${question.options.map((option, optIndex) => {
                                                if(option.type != 'column') return '';
                                                return `
                                                <li class="flex items-center space-x-2">
                                                    <input type="text"
                                                        class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                                        placeholder="Column Content"
                                                        value="${option.text}"
                                                        onkeyup="updateOptionValue('${question.id}', '${optIndex}', this.value)">
                                                    <button class="text-red-600 hover:text-red-800"
                                                        onclick="deleteTableOption('${question.id}', '${optIndex}')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </li>`;
                                            }).join('')}
                                            <li>
                                                <button type="button"
                                                    class="px-3 py-1 border border-yellow-500 text-yellow-500 rounded-md text-sm hover:bg-yellow-50"
                                                    onclick="addTableOption('${question.id}', 'column')">
                                                    <i class="fas fa-plus-circle mr-1"></i>Add New
                                                </button>
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
                $('#sectionBody' + slugText(sectionId)).html('');
                $('#sectionBody' + slugText(sectionId)).html(questionsHtml);
            });

            questions.filter(q => q.id != lastTransactionedQuestion).forEach(q => {
                changeViewQuestion(q.id);
            });
        }

        function deleteTableOption(questionId, optionIndex){
            let question = questions.find(q => q.id == questionId);
            question.options.splice(optionIndex, 1);
            lastTransactionedQuestion = questionId;
            renderQuestions();
        }

        function changeType(sectionId, type) {
            let element = document.getElementById('addQuestionToSectionBtn' + sectionId);
            let oldType = element.getAttribute('question-type');
            element.setAttribute('onclick', `addQuestion('${sectionId}', '${type}')`);
            element.innerHTML = '<i class="fas fa-plus-circle"></i> ' + getTypeText(type);
            checkIconElement(sectionId + '_' + type + '_typeBtn', 'add');
            checkIconElement(sectionId + '_' + oldType + '_typeBtn', 'remove');
            element.setAttribute('question-type', type);
        }

        function checkIconElement(elementId, type) {
            console.log(elementId);
            let element = document.getElementById(elementId);
            if (type == 'add') {
                element.innerHTML = '<i class="fas fa-check"></i> ' + element.innerHTML;
            } else {
                element.innerHTML = element.innerHTML.replace('<i class="fas fa-check"></i> ', '');
            }
        }

        function getTypeText(type) {
            if (type == 'single_choice') {
                return 'Multiple Choice One Answer';
            } else if (type == 'multiple_choice') {
                return 'Multiple Choice Multiple Answer';
            } else if (type == 'open_ended') {
                return 'Open Ended Question';
            }else if (type == 'multiple_choice_table') {
                return 'Multiple Choice Table';
            }
        }

        function editSection(id) {
            let section = sections.find(s => s.id == id);
            let sectionName = section.name;
            let sectionNameDiv = $('#section_name_' + id);
            let sectionNameInput = `<input type="text" class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent" value="${sectionName}" id="sectionNameInput${id}" maxlength="50">`;
            sectionNameDiv.html(sectionNameInput);
            let sectionNameInputEl = $('#sectionNameInput' + id);
            sectionNameInputEl.focus();
            sectionNameInputEl.on('blur', function() {
                let newName = $(this).val();
                section.name = newName;
                renderSections();
            });
        }

        function deleteSection(id) {
            sections = sections.filter(s => s.id != id);
            questions = questions.filter(q => q.section != id);
            lastTransactionedQuestion = id;
            renderSections();
        }

        function updateQuestionValue(questionId, value) {
            let question = questions.find(q => q.id == questionId);
            question.question_text = value;
        }

        function updateOptionValue(questionId, optionIndex, value) {
            let question = questions.find(q => q.id == questionId);
            question.options[optionIndex].text = value;
        }

        function changeReq(questionId, checked) {
            let question = questions.find(q => q.id == questionId);
            question.is_required = checked;
        }

        function copyQuestion(questionId, event) {
            if(event){
                event.stopPropagation();
            }
            let question = questions.find(q => q.id == questionId);
            let newQuestionId = window.crypto.randomUUID();
            let newQuestion = {
                id: newQuestionId,
                section: question.section,
                type: question.type,
                question_text: question.question_text,
                is_required: question.is_required,
                options: question.options.map(option => {
                    return {
                        question_id: newQuestionId,
                        text: option.text,
                    }
                }),
            }
            questions.push(newQuestion);
            lastTransactionedQuestion = newQuestionId;
            renderQuestions();
        }

        function deleteQuestion(questionId, event) {
            if(event){
                event.stopPropagation();
            }
            questions = questions.filter(q => q.id != questionId);
            lastTransactionedQuestion = null;
            renderSections();
        }

        function deleteOption(questionId, optionIndex) {
            let question = questions.find(q => q.id == questionId);
            if (question.options.length <= 2) {
                showToast("2 option must be added at least", 'error');
                return;
            }
            question.options.splice(optionIndex, 1);
            lastTransactionedQuestion = questionId;
            renderQuestions();
        }

        function addOption(questionId) {
            let question = questions.find(q => q.id == questionId);
            question.options.push({
                question_id: questionId,
                text: '',
                type: null,
            });
            lastTransactionedQuestion = questionId;
            renderQuestions();
        }

        function validateDatas() {
            if ($('#name').val() == '') {
                showToast("Title cannot be empty", 'error');
                return false;
            }

            if ($('#name').val().length > 150) {
                showToast("Title cannot be more than 150 characters", 'error');
                return false;
            }

            if ($('#description').val().length > 4000000000) {
                showToast("Description cannot be more than 4000000000 characters", 'error');
                return false;
            }

            if (sections.filter(s => s.name != null).length == 0) {
                showToast("At least 1 section must be added", 'error');
                return false;
            }
            if (questions.filter(q => q.type != 'entry_question').length == 0) {
                showToast("At least 1 question must be added", 'error');
                return false;
            }

            let isValid = true;

            questions.forEach((question, index) => {
                if (question.question_text == '' || question.question_text.length > 4000000000) {
                    showToast("Option cannot be more than 4000000000 characters " + (index + 1) + ".Question)", 'error');
                    isValid = false;
                    return;
                }
                if ((question.type == 'single_choice' || question.type == 'multiple_choice') && question.options.length < 2) {
                    showToast("At least 2 option must be added", 'error');
                    isValid = false;
                    return;
                }
                if(question.options){
                    question.options.forEach((option, index) => {
                    if (option.text == '' || option.text.length > 4000000000) {
                        showToast("Option cannot be more than 4000000000 characters, " + (index + 1) + ".Question, " + (question.options.indexOf(option) + 1) + ".Option", 'error');
                        isValid = false;
                        return;
                    }
                });
                }
                if (!isValid) return;
            });

            if (!isValid) return false;

            sections.forEach(section => {
                if (questions.filter(q => q.section == section.id).length == 0) {
                    sections = sections.filter(s => s.id != section.id);
                } else {
                    if (section.name == '' || section.name.length > 150) {
                        showToast("Section name must be less than 150 characters", 'error');
                        isValid = false;
                        return;
                    }
                }
                if (!isValid) return;
            });

            return isValid;
        }

        function submitForm() {
            if (validateDatas()) {
                document.getElementById('saveBtn').disabled = true;
                document.getElementById('saveBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving';

                let surveyFormData = new FormData($('#surveyForm')[0]);
                let groupedQuestions = {};
                questions.forEach(question => {
                    let sectionName;
                    if (question.section == null) {
                        sectionName = 'null';
                    } else {
                        sectionName = sections.find(s => s.id == question.section).name;
                    }
                    if (!groupedQuestions[sectionName]) {
                        groupedQuestions[sectionName] = [];
                    }
                    groupedQuestions[sectionName].push(question);
                });
                surveyFormData.append('sectionHasQuestions', JSON.stringify(groupedQuestions));
                $.ajax({
                    url: $('#surveyForm').attr('action'),
                    type: 'POST',
                    data: surveyFormData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status == 'success') {
                            showToast(response.message, 'success');
                            setTimeout(() => {
                                window.location.href = response.redirect_url;
                            }, 1000);
                        } else {
                            showToast(response.message, 'error');
                        }
                    },
                    error: function(err) {
                        showToast("An error occurred. Please try again later.", 'error');
                    }
                });
            }
        }

        function addEntryQuestion(){
            let question = {
                id: window.crypto.randomUUID(),
                section: null,
                type: 'entry_question',
                question_text: '',
                is_required: false,
                options: [],
            }
            questions.push(question);
        }

        function slugText(text){
            if(!text){
                return text;
            }
            return text.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
        }

        function changeViewQuestion(questionId, event){
            if(event){
                event.stopPropagation();
            }

            if(!questions.find(q => q.id == questionId)){
                return;
            }

            if(questions.find(q => q.id == questionId).type == 'entry_question'){
                return;
            }

            let element = document.getElementById('changeViewBtn' + questionId);
            let visibility = element.getAttribute('visibility');
            let divs = document.querySelectorAll('.qDiv' + questionId);
            if(visibility == 'true'){
                divs.forEach(div => {
                    div.classList.add('hidden');
                });
                element.innerHTML = '<i class="fas fa-eye"></i>';
                element.setAttribute('visibility', 'false');
                document.getElementById('qText' + questionId).classList.remove('pb-0');
            }else{
                divs.forEach(div => {
                    div.classList.remove('hidden');
                });
                element.innerHTML = '<i class="fas fa-eye-slash"></i>';
                element.setAttribute('visibility', 'true');
                document.getElementById('qText' + questionId).classList.add('pb-0');
            }
        }

    </script>
@endsection

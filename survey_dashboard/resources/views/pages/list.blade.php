@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-5">
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-cyan-500">Surveys</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6" id="gridDiv"></div>
        </div>
    </div>

    <script>
        let start = 0;
        let length = 16;

        document.addEventListener('DOMContentLoaded', function() {
            loadData();
        });


        function loadData() {
            if (document.getElementById('infinite_scroll_div')) {
                document.getElementById('infinite_scroll_div').remove();
            }

            $.ajax({
                url: '{{ route('api.surveys') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    start: start,
                    length: length
                },
                success: function(response) {
                    if(response.data.length > 0){
                        response.data.forEach((survey, index) => {
                            const card = document.createElement('div');
                            card.classList.add('bg-gray-800', 'rounded-lg', 'shadow-md', 'overflow-hidden');
                            card.innerHTML = `
                                <div class="relative">
                                    <img src="/laravel_files/survey.png" alt="Card Image" class="w-full h-32 object-cover">
                                    <button onclick="copyClipboard('{{ env('APP_URL') }}/surveys/${survey.share_link}')" class="absolute top-14 right-2 bg-gray-800 text-white rounded-full p-2 hover:bg-gray-700">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <a href="/survey/${survey.id}" class="absolute top-2 right-2 bg-gray-800 text-white rounded-full p-2 hover:bg-gray-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                                <div class="p-4">
                                    <h2 class="text-lg font-bold text-gray-100">${survey.title}</h2>
                                    <p class="text-sm text-gray-400 mt-2">${survey.description ?? 'No Description'}</p>
                                </div>
                            `;

                            document.querySelector('#gridDiv').appendChild(card);

                            if (index == 11 && response.data.length == 16) {
                                $('#gridDiv').append(
                                    `<div class=" col-span-1 sm:col-span-2 lg:col-span-5" style="height:1px;background-color:#00000000" id="infinite_scroll_div"></div>`
                                );
                            }
                        });
                        if (response.data.length == 16) {
                            start += 16;
                            observeElement('infinite_scroll_div');
                        }
                    }
                },
                error: function(err) {
                    showToast("An error occurred. Please try again later.", 'error');
                }
            });
        }

        function observeElement(div) {
            const targetElement = document.getElementById('infinite_scroll_div');
            observerTargetElement = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        loadData();
                    }
                });
            });

            observerTargetElement.observe(targetElement);
        }

        function copyClipboard(text) {
            console.log(text);
            navigator.clipboard.writeText(text).then(() => {
                showToast('Link copied to clipboard!');
            }).catch(() => {
                showToast('An error occurred while copying the link.', 'error');
            });
        }
    </script>
@endsection

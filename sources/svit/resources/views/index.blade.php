<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>SoftSvit</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
              integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    </head>
    <body class="antialiased">
    <div>
        <form
            class="pt-2 pl-3"
            id="uploadForm"
            method="post"
            action="/upload"
            enctype="multipart/form-data">
            <input class="form-control d-none" type="file" id="directoryInput" webkitdirectory directory multiple>
            <div class="pt-2" id="fileList"></div>
            <div class="pt-2">
                <button
                    class="btn btn-primary"
                    id="addBtn"
                    type="button">
                    Add files
                </button>
                <button
                    class="btn btn-success"
                    id="submitBtn"
                    type="button">
                    Send
                </button>
            </div>
        </form>
        <div class="pt-2" id="linkList"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"></script>
    </body>
    <script>
        let sqlFiles = [];
        let linksFromServer = [];

        document.getElementById('directoryInput').addEventListener('change', function (e) {
            let fileList = e.target.files;
            let fileListDiv = document.getElementById('fileList');

            for (let i = 0; i < fileList.length; i++) {
                let file = fileList[i];
                if (file.name.endsWith('.sql')) {
                    let listItem = document.createElement('div');
                    listItem.classList.add('pt-2');

                    let checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.value = file.name;
                    checkbox.id = 'file_' + i;

                    listItem.appendChild(checkbox);

                    let label = document.createElement('label');
                    label.textContent = file.name;
                    label.setAttribute('for', 'file_' + i);

                    listItem.appendChild(label);
                    fileListDiv.appendChild(listItem);

                    checkbox.addEventListener('change', function () {
                        if (this.checked) {
                            sqlFiles.push(file);
                        } else {
                            let index = sqlFiles.indexOf(file);
                            if (index > -1) {
                                sqlFiles.splice(index, 1);
                            }
                        }
                    });
                }
            }
        });

        document.getElementById('addBtn').addEventListener('click', function () {
            document.getElementById('directoryInput').click();
        });

        document.getElementById('submitBtn').addEventListener('click', function () {
            let formData = new FormData();

            sqlFiles.forEach((file) => {
                formData.append('sqlFiles[]', file);
            });

            let url = "{{ route('api.csv.createFromSql') }}";
            let request = createRequest(url, 'POST', formData);

            fetch(request)
                .then(response => response.json())
                .then(data => {
                    renderLinkList(data);
                })
                .catch(error => {
                    // Обработка ошибки
                });
        });

        function createRequest(url, method, body) {
            const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
            const headers = new Headers({
                'X-CSRF-TOKEN': csrfToken
            });

            return new Request(url, {
                method: method,
                headers: headers,
                body: body
            });
        }

        function renderLinkList(links) {
            linksFromServer = [...links];
            let linkListDiv = document.getElementById('linkList');

            for (let i = 0; i < links.length; i++) {
                let listItem = document.createElement('div');
                listItem.textContent = links[i];
                listItem.classList.add('pointer');
                listItem.classList.add('pt-2');

                listItem.addEventListener('click', function() {
                    window.location.href = links[i];
                });

                linkListDiv.appendChild(listItem);
            }

            let mergeButton = document.createElement('button');
            mergeButton.textContent = "Merge CSV's";
            mergeButton.id = 'mergeBtn';
            linkListDiv.appendChild(mergeButton);

            mergeButton.addEventListener('click', function () {
                mergeCsv();
            });
        }

        function mergeCsv() {
            let url = "{{ route('api.csv.merge') }}";
            let formData = new FormData();

            linksFromServer.forEach((link) => {
                formData.append('links[]', link);
            });

            let request = createRequest(url, 'POST', formData);

            fetch(request)
                .then(response => response.json())
                .then(data => {
                    let linkListDiv = document.getElementById('linkList');
                    let listItem = document.createElement('div');
                    listItem.textContent = data;
                    listItem.classList.add('pointer');
                    linkListDiv.appendChild(listItem);
                })
                .catch(error => {
                    console.error('Error adding link:', error);
                });
        }
    </script>
    <style>
        .pointer {
            cursor: pointer;
        }
    </style>
</html>

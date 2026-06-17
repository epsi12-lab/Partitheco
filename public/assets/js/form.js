// assets/js/form.js

document.querySelector('form')
    ?.addEventListener('submit', function (e) {
        console.log(e);
        e.preventDefault();

        let hasError = false;

        const title = document.getElementById('title');
        if (title.value === '' || title.value.length > 100) {
            hasError = true;
            title.classList.add('error');
            title.nextElementSibling.innerText = 'Le titre doit avoir entre 1 et 100 caractères';

            title.addEventListener('input', () => {
                if (title.value !== '' && title.value.length <= 100) {
                    if (title.classList.contains('error')) {
                        title.classList.remove('error');
                        title.nextElementSibling.innerText = '';
                    }
                } else {
                    title.classList.add('error');
                    title.nextElementSibling.innerText = 'Le titre doit avoir entre 1 et 100 caractères';
                }
            });
        }

        const description = document.getElementById('description');
        if (description.value === '') {
            hasError = true;
            description.classList.add('error');
            description.nextElementSibling.innerText = 'La description est obligatoire';

            description.addEventListener('input', () => {
                if (description.value !== '') {
                    if (description.classList.contains('error')) {
                        description.classList.remove('error');
                        description.nextElementSibling.innerText = '';
                    }
                } else {
                    description.classList.add('error');
                    description.nextElementSibling.innerText = 'La description est obligatoire';
                }
            });
        }

        if (!hasError) {
            this.submit();
        }
    });

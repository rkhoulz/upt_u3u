document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('form');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var requiredFields = form.querySelectorAll('[required]');
            var emptyField = null;

            requiredFields.forEach(function (field) {
                var value = (field.value || '').trim();
                if (value === '' && emptyField === null) {
                    emptyField = field;
                }
            });

            if (emptyField !== null) {
                event.preventDefault();
                alert('Por favor completa todos los campos obligatorios antes de continuar.');
                emptyField.focus();
            }
        });
    });
});

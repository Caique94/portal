$(document).ready(function() {

    // ========== AJAX SETUP GLOBAL ==========
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'  // Força retorno em JSON
        },
        error: function(xhr, status, error) {
            // Handle AJAX errors globally
            if (xhr.status === 401) {
                console.error('Erro 401: Sessão expirada ou não autenticado');
                // Redirecionar para login se necessário
                // window.location.href = '/login';
            } else if (xhr.status === 403) {
                console.error('Erro 403: Acesso negado');
            }
        }
    });

    /* MOBILE SIDEBAR TOGGLE */
    $('#sidebarToggle').on('click', function() {
        $('#main-wrapper').toggleClass('sidebar-open');
        $('.left-sidebar').toggleClass('show');
    });

    /* Close sidebar when a link is clicked on mobile */
    $('.left-sidebar .sidebar-link').on('click', function() {
        if ($(window).width() < 1200) {
            $('#main-wrapper').removeClass('sidebar-open');
            $('.left-sidebar').removeClass('show');
        }
    });

    /* Close sidebar when clicking the overlay */
    $('#main-wrapper').on('click', function(e) {
        if ($(e.target).is('#main-wrapper')) {
            $('#main-wrapper').removeClass('sidebar-open');
            $('.left-sidebar').removeClass('show');
        }
    });

    /* Handle window resize */
    $(window).on('resize', function() {
        if ($(window).width() >= 1200) {
            $('#main-wrapper').removeClass('sidebar-open');
            $('.left-sidebar').removeClass('show');
        }
    });

    /*
    $(document).ajaxError(function(event, xhr, settings, thrownError) {
        if (xhr.status === 401) {
            window.location.replace('/login');
        }
    });
    */

    /* REDEFINIR SENHA */
    $('.btn-redefinir-senha').on('click', function() {
        let html = '';
        html += '<input type="password" id="senha_atual" class="swal2-input" placeholder="Senha Atual">';
        html += '<input type="password" id="nova_senha" class="swal2-input" placeholder="Nova Senha">';
        html += '<input type="password" id="confirmar_senha" class="swal2-input" placeholder="Confirmar Senha">';

        Swal.fire({
            title: "Redefinir Senha",
            html: html,
            showCancelButton: true,
            confirmButtonText: 'Salvar',
            cancelButtonText: 'Cancelar',
            backdrop: false,
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                var password = $('#nova_senha').val();
                var confirmPassword = $('#confirmar_senha').val();

                if (password === confirmPassword) {
                    console.log('1');
                    //$('#password_match_message').hide();
                } else {
                    console.log('2');
                    //$('#password_match_message').show();
                }
            }
        });
    });

    /* VALORES DEFAULT PARA DATATABLES */
    $.extend( true, $.fn.dataTable.defaults, {
        processing: true,
        layout: {
            topStart: ['buttons', 'pageLength']
        },
        language: {
            url: '/plugins/datatables/i18n/pt-BR.json',
            processing: ''
        }
    });

    document.querySelectorAll('.modal').forEach((modal) => {
        modal.addEventListener('hide.bs.modal', () => {
            // Blur the currently active (focused) element when the modal is hiding
            document.activeElement.blur();
        });
    });

    $('.cpf-cnpj').mask(CpfCnpjMaskBehavior, cpfCnpjpOptions);

    // Load static tooltips
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    document.querySelectorAll('.modal').forEach((modal) => {
        modal.addEventListener('shown.bs.modal', function () {
            var tooltipTriggerList = [].slice.call(modal.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    container: 'body' // Important for tooltips inside modals
                });
            });
        });
    });

    /* Custom Masks */
    $('.mask-km').mask('#0.00', { reverse: true });
    $('.mask-deslocamento').mask('#0:00', { reverse: true });
    $('.mask-aniversario').mask('00/00');
    $('.money').mask("#.##0,00", {reverse: true});

    var SPMaskBehavior = function (val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    },
    spOptions = {
        onKeyPress: function(val, e, field, options) {
            field.mask(SPMaskBehavior.apply({}, arguments), options);
        }
    };

    $('.phone').mask(SPMaskBehavior, spOptions);

});

const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

var CpfCnpjMaskBehavior = function (val) {
    return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
},
cpfCnpjpOptions = {
    onKeyPress: function(val, e, field, options) {
        field.mask(CpfCnpjMaskBehavior.apply({}, arguments), options);
    }
};

// Load dynamic tooltips
function initializeTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}

function today() {
    var today = new Date();

    // Get the year, month, and day
    var year = today.getFullYear();
    var month = today.getMonth() + 1; // Months are 0-indexed, so add 1
    var day = today.getDate();

    // Format month and day to always have two digits (e.g., 01, 09)
    if (month < 10) {
        month = '0' + month;
    }
    if (day < 10) {
        day = '0' + day;
    }

    // Construct the date string in YYYY-MM-DD format
    var formattedDate = year + '-' + month + '-' + day;

    // Set the value of the date input field
    // Replace '#yourDateInputId' with the actual ID of your input element
    return formattedDate;
}

function validateFormRequired(form) {
    let is_valid = true;

    $(form).find('.is-invalid').removeClass('is-invalid');

    let validation_msg = '';
    let count = 0
    $(form).find('[required]').each(function() {
        if ($(this).val() == '') {
            var inputId = $(this).attr("id");
            var labelText = $("label[for='" + inputId + "']").text();
            $(this).addClass('is-invalid');
            validation_msg += (validation_msg == '' ? '' : ', ') + labelText;
            count++;
        }
    });

    if (validation_msg != '') {
        var msg = count > 1 ? 'Os seguintes campos s&atilde;o obrigat&oacute;rios: '  + validation_msg : 'O seguinte campo &eacute; obrigat&oacute;rio: ' + validation_msg;

        Swal.fire({
            icon: 'error',
            title: msg,
            showConfirmButton: false,
            toast: true,
            position: "top-end",
            timer: 3000,
            timerProgressBar: true
        });

        is_valid = false;
    }

    return is_valid;
}

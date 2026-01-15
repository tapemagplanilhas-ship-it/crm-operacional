// editar_usuario.js
$(document).ready(function() {
    // Mostrar/ocultar senha
    $('.toggle-password').click(function() {
        const field = $(this).prev('input');
        const icon = $(this).find('i');
        
        if (field.attr('type') === 'password') {
            field.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            field.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Verificar força da senha
    $('#nova_senha').on('input', function() {
        const password = $(this).val();
        const strengthDiv = $('#password-strength');
        const strengthText = $('#strength-text');
        const strengthFill = $('#strength-fill');
        
        if (password.length === 0) {
            strengthDiv.hide();
            return;
        }
        
        strengthDiv.show();
        
        // Calcular força
        let strength = 0;
        
        // Comprimento
        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        
        // Tipos de caracteres
        if (/[a-z]/.test(password)) strength += 1;
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;
        
        // Atualizar visual
        strengthFill.removeClass('strength-weak strength-medium strength-strong');
        
        if (strength <= 2) {
            strengthText.text('Fraca');
            strengthFill.addClass('strength-weak');
        } else if (strength <= 4) {
            strengthText.text('Média');
            strengthFill.addClass('strength-medium');
        } else {
            strengthText.text('Forte');
            strengthFill.addClass('strength-strong');
        }
    });
    
    // Validar senhas
    $('#confirmar_senha').on('blur', function() {
        const senha = $('#nova_senha').val();
        const confirmar = $(this).val();
        
        if (senha !== '' && confirmar !== '' && senha !== confirmar) {
            $(this).addClass('error');
            showToast('As senhas não coincidem!', 'error');
        } else {
            $(this).removeClass('error');
        }
    });
    
    // Enviar formulário
    $('#form-editar-usuario').submit(function(e) {
        e.preventDefault();
        
        // Validações
        const formData = {
            id: $('#usuario_id').val(),
            nome: $('#nome').val(),
            usuario: $('#usuario').val(),
            email: $('#email').val(),
            perfil: $('#perfil').val(),
            senha_atual: $('#senha_atual').val(),
            nova_senha: $('#nova_senha').val(),
            confirmar_senha: $('#confirmar_senha').val()
        };
        
        // Validar senha
        if (formData.nova_senha !== '' && formData.nova_senha !== formData.confirmar_senha) {
            showToast('As senhas não coincidem!', 'error');
            return;
        }
        
        if (formData.nova_senha !== '' && formData.nova_senha.length < 8) {
            showToast('A nova senha deve ter pelo menos 8 caracteres!', 'error');
            return;
        }
        
        // Enviar via AJAX
        $.ajax({
            url: 'processar_editar_usuario.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
            },
            success: function(response) {
                if (response.success) {
                    showToast('Usuário atualizado com sucesso!', 'success');
                    setTimeout(function() {
                        window.location.href = 'admin_usuarios.php';
                    }, 1500);
                } else {
                    showToast(response.message || 'Erro ao atualizar usuário!', 'error');
                    $('button[type="submit"]').prop('disabled', false)
                        .html('<i class="fas fa-save"></i> Salvar Alterações');
                }
            },
            error: function() {
                showToast('Erro na conexão com o servidor!', 'error');
                $('button[type="submit"]').prop('disabled', false)
                    .html('<i class="fas fa-save"></i> Salvar Alterações');
            }
        });
    });
    
    // Função para mostrar toast
    function showToast(message, type) {
        const toast = $('<div class="toast ' + type + '">' +
            '<i class="fas fa-' + (type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle') + '"></i>' +
            '<span>' + message + '</span>' +
            '</div>');
        
        $('#toast-container').append(toast);
        
        setTimeout(function() {
            toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
});
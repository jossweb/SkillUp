function toggleForm(toregister){
    var loginForm = document.getElementById('login-form');
    var registerForm = document.getElementById('register-form');
    if(toregister){
        toggleIsSelected(true, loginForm, registerForm);
        registerForm.style.display = 'flex';
        loginForm.style.display = 'none';
    }else{
        toggleIsSelected(false, loginForm, registerForm);

        registerForm.style.display = 'none';
        loginForm.style.display = 'flex';
    }
}
function toggleIsSelected(registerIsClicked){
    var loginBtn = document.getElementById('login-btn');
    var registerBtn = document.getElementById('register-btn');
    if(registerIsClicked){
        loginBtn.classList.remove('isSelected');
        registerBtn.classList.add('isSelected');
    }else{
        loginBtn.classList.add('isSelected');
        registerBtn.classList.remove('isSelected');
    }
}
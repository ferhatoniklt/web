<?php require_once 'header.php'; ?>

<div class="sign section--bg" data-bg="img/section/section.jpg">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="sign__content">
                    <form action="signin_op.php" method="POST" class="sign__form">
                        <a href="index.php" class="sign__logo">
                            <img src="img/logo.png" alt="">
                        </a>

                        <div class="sign__group">
                            <input type="email" name="mail" class="sign__input" placeholder="Email" required>
                        </div>

                        <div class="sign__group">
                            <input type="password" name="password" class="sign__input" placeholder="Password" required>
                        </div>

                        <div class="sign__group sign__group--checkbox">
                            <input id="remember" name="remember" type="checkbox" checked="checked">
                            <label for="remember">Remember Me</label>
                        </div>
                        
                        <button class="sign__btn" type="submit">SIGN IN</button>

                        <span class="sign__text">Don't have an account? <a href="signup.php">Register now!</a></span>

                        <span class="sign__text"><a href="#">Forgot password?</a></span>
                    </form>
                    </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
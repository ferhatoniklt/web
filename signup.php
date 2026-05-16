<?php require_once 'header.php'; ?>

<div class="sign section--bg" data-bg="img/section/section.jpg">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="sign__content">
                    <form action="signup_op.php" method="POST" class="sign__form">
                        <a href="index.php" class="sign__logo">
                            <img src="img/logo.png" alt="">
                        </a>

                        <div class="sign__group">
                            <input type="text" name="name" class="sign__input" placeholder="Full Name" required>
                        </div>

                        <div class="sign__group">
                            <input type="email" name="mail" class="sign__input" placeholder="Email" required>
                        </div>

                        <div class="sign__group">
                            <input type="password" name="password" class="sign__input" placeholder="Password" required>
                        </div>

                        <div class="sign__group sign__group--checkbox">
                            <input id="policy" name="policy" type="checkbox" checked="checked" required>
                            <label for="policy">I agree to the <a href="#">Privacy Policy</a></label>
                        </div>
                        
                        <button class="sign__btn" type="submit">SIGN UP</button>

                        <span class="sign__text">Already have an account? <a href="signin.php">Sign In!</a></span>
                    </form>
                    </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
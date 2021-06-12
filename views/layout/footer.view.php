
    <footer class="page-footer">
        <?php if (isset($room_link) && !empty($room_link)):?>
        <div class="room-link">
            <input type="hidden" id="room-link" value="<?=$room_link?>">
            <div class="room-link-text">Share Room Link</div>
            <div class="room-link-icon"><i class="fa fa-copy"></i></div>
        </div>

        <script>
            function copy(text) {
                var input = document.createElement('input');
                input.setAttribute('value', text);
                document.body.appendChild(input);
                input.select();
                var result = document.execCommand('copy');
                document.body.removeChild(input);
                return result;
            }

            $('.room-link').on('click', (e) => {
                copy($('#room-link').val());
            });
        </script>

        <?php endif; ?>

        <div class="page-footer-copyright">
            <p>Copyright Example.com</p>
        </div>
        <div class="page-footer-button">
            <input type="checkbox" name="mode" id="mode">
            <label for="mode">
                <span class="bg">
                    <span class="bg-button"></span>
                </span>
                <div class="mode-text">dark</div>
            </label>
        </div>
    </footer>

</div>

<script>
    $('#mode').on('change', (e) => {
        if (e.target.checked) {
            $('body').addClass('dark');
            localStorage.setItem('mode', 'dark');
        } else {
            $('body').removeClass('dark');
            localStorage.setItem('mode', 'light');
        }
    });
    
    if (localStorage.getItem('mode') !== undefined) {

        let mode = localStorage.getItem('mode');

        if (mode === 'light') {
            $('body').removeClass('dark');
            $('#mode').removeAttr('checked');
        } else {
            $('body').addClass('dark');
            $('#mode').attr('checked', 'false');
        }

    } else {
        localStorage.setItem('mode', 'light');
    }
</script>

</body>
</html>
                    <div id="toastsContainer"></div>

                </main>
            </div>
        </div>

        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script>
            const HTTP_STATUS       = <?= json_encode(HTTP_STATUS);         ?>;
            const IMAGE_UPLOAD_PATH = <?= json_encode(IMAGE_UPLOAD_PATH);   ?>;
        </script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

        <!-- Icons -->
        <script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
        <script> feather.replace(); </script>

        <!-- Graphs -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>

        <!-- Validator -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/validator/13.5.2/validator.min.js" integrity="sha512-vZEoK8xRncku4g5GANHh5zobUjeCMbZkSEahrADeAcRlk/1Ignf8sAKojkV4RZLkPw145+ILJFisI2pnjsPtGQ==" crossorigin="anonymous"></script>

        <!-- Custom scripts -->
        <script src="assets/js/index.js"></script>
    </body>
</html>
<?php
// ヘッダーテンプレート
require_once 'footer.php';

function print_book_footer(){
print_footer();
$footer = <<< DOC
</div><!-- //#wrapper -->


</body>
</html>
DOC;

print($footer);
}
?>
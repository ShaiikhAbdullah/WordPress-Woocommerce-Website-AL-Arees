<?php

namespace Tenweb_Authorization {

    interface ProductActions
    {
        public function activate();

        public function deactivate();

        public function update();

        public function delete();

    }

}

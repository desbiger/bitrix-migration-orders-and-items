<?php


interface Exporter {
    /**
     * @return $this;
     */
    public function before();

    /**
     * @return $this
     */
    public function execute();

    public function after();
}
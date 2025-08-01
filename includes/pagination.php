<?php
class Pagination {
    private $total_records;
    private $records_per_page;
    private $current_page;
    private $total_pages;
    private $offset;
    
    public function __construct($total_records, $records_per_page = 10, $current_page = 1) {
        $this->total_records = (int)$total_records;
        $this->records_per_page = (int)$records_per_page;
        $this->current_page = max(1, (int)$current_page);
        $this->total_pages = ceil($this->total_records / $this->records_per_page);
        $this->current_page = min($this->current_page, $this->total_pages);
        $this->offset = ($this->current_page - 1) * $this->records_per_page;
    }
    
    public function getOffset() {
        return $this->offset;
    }
    
    public function getLimit() {
        return $this->records_per_page;
    }
    
    public function getCurrentPage() {
        return $this->current_page;
    }
    
    public function getTotalPages() {
        return $this->total_pages;
    }
    
    public function getTotalRecords() {
        return $this->total_records;
    }
    
    public function hasNextPage() {
        return $this->current_page < $this->total_pages;
    }
    
    public function hasPreviousPage() {
        return $this->current_page > 1;
    }
    
    public function getNextPage() {
        return $this->hasNextPage() ? $this->current_page + 1 : $this->current_page;
    }
    
    public function getPreviousPage() {
        return $this->hasPreviousPage() ? $this->current_page - 1 : $this->current_page;
    }
    
    public function generatePaginationLinks($base_url, $additional_params = []) {
        if ($this->total_pages <= 1) {
            return '';
        }
        
        $params = http_build_query($additional_params);
        $separator = empty($params) ? '?' : '&';
        
        $html = '<nav aria-label="Page navigation">';
        $html .= '<ul class="pagination justify-content-center">';
        
        // Previous button
        if ($this->hasPreviousPage()) {
            $prev_url = $base_url . ($params ? '?' . $params : '') . $separator . 'page=' . $this->getPreviousPage();
            $html .= '<li class="page-item"><a class="page-link" href="' . $prev_url . '">Previous</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }
        
        // Page numbers
        $start_page = max(1, $this->current_page - 2);
        $end_page = min($this->total_pages, $this->current_page + 2);
        
        if ($start_page > 1) {
            $first_url = $base_url . ($params ? '?' . $params : '') . $separator . 'page=1';
            $html .= '<li class="page-item"><a class="page-link" href="' . $first_url . '">1</a></li>';
            if ($start_page > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $this->current_page) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $page_url = $base_url . ($params ? '?' . $params : '') . $separator . 'page=' . $i;
                $html .= '<li class="page-item"><a class="page-link" href="' . $page_url . '">' . $i . '</a></li>';
            }
        }
        
        if ($end_page < $this->total_pages) {
            if ($end_page < $this->total_pages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $last_url = $base_url . ($params ? '?' . $params : '') . $separator . 'page=' . $this->total_pages;
            $html .= '<li class="page-item"><a class="page-link" href="' . $last_url . '">' . $this->total_pages . '</a></li>';
        }
        
        // Next button
        if ($this->hasNextPage()) {
            $next_url = $base_url . ($params ? '?' . $params : '') . $separator . 'page=' . $this->getNextPage();
            $html .= '<li class="page-item"><a class="page-link" href="' . $next_url . '">Next</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    public function getRecordInfo() {
        if ($this->total_records == 0) {
            return 'No records found';
        }
        
        $start = $this->offset + 1;
        $end = min($this->offset + $this->records_per_page, $this->total_records);
        
        return "Showing {$start} to {$end} of {$this->total_records} entries";
    }
}
?>

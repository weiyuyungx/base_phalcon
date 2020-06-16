<?php
namespace app\libary;

class Page
{
    private $total;  //总条数
    private $cur_page;  //当前页，最小值1
    private $page_size = 10;  //每页条数 
    private $first_page = 1;
    private $max_page;  //最大页
    private $next_page;  //下一页
    private $pre_page;  //上一页
    private $get = [];  //get请求
    
    
    /** 构造
     * @author  WYY 2018年10月9日 下午5:19:33
     * @param int $total
     * @param number $cur_page
     * @param number $page_size
     */
    public function __construct($total = 0 , $cur_page =1 ,$page_size = 10)
    {
        $this->setTotal($total);
        $this->setCur_page($cur_page);
        $this->setPage_size($page_size);

        
        $this->counting();

    }
    
    public function counting()
    {
        $this->max_page = ceil($this->total/$this->page_size);
        $this->next_page = min($this->max_page , $this->cur_page +1);
        $this->pre_page = max(1,$this->cur_page -1);
    }
    
    
    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return mixed
     */
    public function getCur_page()
    {
        return $this->cur_page;
    }

    /**
     * @return mixed
     */
    public function getPage_size()
    {
        return $this->page_size;
    }

    /**
     * @return number
     */
    public function getFirst_page()
    {
        return $this->first_page;
    }

    /**
     * @return number
     */
    public function getMax_page()
    {
        return $this->max_page;
    }

    /**
     * @return mixed
     */
    public function getNext_page()
    {
        return $this->next_page;
    }

    /**
     * @return mixed
     */
    public function getPre_page()
    {
        return $this->pre_page;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = max($total , 0);
        $this->counting();
    }

    /**
     * @param mixed $cur_page
     */
    public function setCur_page($cur_page)
    {
        $this->cur_page = max($cur_page,1);
    }

    /**
     * @param mixed $page_size
     */
    public function setPage_size($page_size)
    {
        if ($page_size > 0)
          $this->page_size = $page_size;
    }

    /**
     * @param number $first_page
     */
    public function setFirst_page($first_page)
    {
        $this->first_page = $first_page;
    }

    /**
     * @param number $max_page
     */
    public function setMax_page($max_page)
    {
        $this->max_page = $max_page;
    }

    /**
     * @param mixed $next_page
     */
    public function setNext_page($next_page)
    {
        $this->next_page = $next_page;
    }

    /**
     * @param mixed $pre_page
     */
    public function setPre_page($pre_page)
    {
        $this->pre_page = $pre_page;
    }

    public function toArray() 
    {
        $data['cur_page'] = $this->cur_page;
        $data['first_page'] = $this->first_page;
        $data['max_page'] = $this->max_page;
        $data['next_page'] = $this->next_page;
        $data['pre_page'] = $this->pre_page;
        $data['page_size'] = $this->page_size;
        $data['total'] = $this->total;
        
        return $data;
    }

    
    public function limit() 
    {
        return 'limit '.($this->cur_page -1)*$this->page_size.','.$this->page_size;
    }
    
    public function offset() 
    {
        return ($this->getCur_page() - 1) * $this->getPage_size();
    }
    
    /** 设置get
     * @author  WYY 2019年1月5日 下午1:20:21
     * @param array $get get请求
     */
    public function setGet($get) 
    {
        $this->get = $get;
    }
    
    
    
    /** 生成页码UR
     * @author  WYY 2019年1月5日 下午1:21:12
     * @param int $i_page
     */
    public function getPageUrl($i_page)
    {
        $str = './index.php?';
        foreach ($this->get as $k=>$v )
        {
            $str .= "{$k}={$v}&";
        }
        
        $str .= 'cur_page='.$i_page;
        
        return $str;
    }
    
    
    public function getMaxPageUrl()
    {
        return $this->getPageUrl($this->max_page);
    }
    
    public function getNextPageUrl() 
    {
        return $this->getPageUrl($this->next_page);
    }
    
    public function getPrePageUrl() 
    {
        return $this->getPageUrl($this->pre_page);
    }
    
    
    
    
}


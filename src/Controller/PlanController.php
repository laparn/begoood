<?php

/**
 * Description of PlanController
 *
 * @author juliettedompe, laurinf
 * @package Controller
 */
class PlanController extends Controller {

    /**
     *  PlanController Constructor
     *
     *  make a new PlanController
     */
    public function __construct() {

        $this->class = 'Plan';
    }
    
    
   
    /**
     * 
     * lists the existing links between a Plan and another class $class
     * @param String $id the Plan identifier
     * @param String $class the linked class. 
     * values it can take: 'reports', 'tests'
     * @return Array an array of the linked elements
     */
    function listLinks($id, $class) {
        switch ($class) {
            case 'tests':
                return $this->listTests($id);
                break;
            case 'reports':
                return $this->listReports($id);
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * list the tests contained in this plan
     * 
     * @param string $id the plan identifier
     * @return Array[Test] an array of tests related to the plan
     */
    public function listTests($id) {
        $plan = new Plan();
        $p = $plan->findById($id);
        return $p->getTests();
    }

    /**
     * list all the reports related to this plan
     * 
     * @param string $id the plan identifier
     * @return Array[Report] an array of the related reports
     */
    public function listReports($id) {
        $res = array();
        $r = new Report();
        $res = $r->find(array('conditions' => array(array('id-p' => $id))));
        return $res;
    }

    /**
     * runs a plan of tests. The execution of this method will result
     * in new reports related to the plan
     * 
     * @param string $id the plan identifier
     * @return Report the main plan report generated by this plan execution
     */
     public function run($id) {
        $plan = new Plan();
        $p = $plan->findById($id);
       // return new Affiche(json_encode($p));
        if (!is_null($p))
            return $this->executePlan($p);
     }
     
     
    /**
     * 
     * executes all tests belonging to a plan $p
     * 
     * @param Plan $p the plan to execute
     * @return Report the report generated by the execution of the plan $p
     */
protected function executePlan($p) {
        $tests = $p->getTests();
        if (is_null($tests)) {
            return ("Plan $p contains no tests to run"); //we should return a void report?
        }
        $pReport = new PlanReport();
        $tc = new TestController();
        $data = array();
        $data['id-p']=$p->getOid();
        $data['exec-date'] = date("Y-m-d H:i:s");
        $results = array();
        foreach ($tests as $test) { 
            $result = $tc->executeTest($test,$p->getOid());
            if (is_string($result)) { //to make sure execute-test is returning un objet and not an error 
                return $result;
            }
            $results[] = $result;
        }
        //Obtention des données pour le rapport
        $content = '';
        foreach ($results as $res) {
            $content .= $res->getAttr('id-t') . ": " . $res->getAttr('content') . "\n";
            $boolRes[] = $res->getAttr('result');
        }
        $data['content'] = $content;
        if (array_search(false, $boolRes) === false) {
            $data['result'] = true;
        } else {
            $data['result'] = false;
        }
        $pReport->create($data);
        return $pReport;
        
      
    }
}

?>
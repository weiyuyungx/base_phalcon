{{php_tag}}

namespace {{name_space}};


/**
 * #
 * @author ({{author}})
 */
class {{model_name}} extends {{parent_class}}

{


	{% for field in fields %}
	{{field}}
	{% endfor %}
	
	
	/**
     * #构造
     * {@inheritDoc}
     * @see \app\base\BaseModel::onConstruct()
     */
    public function onConstruct() 
    {
        parent::onConstruct();
    }


}
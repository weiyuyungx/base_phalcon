{{php_tag}}

namespace {{name_space}};


/**
 *
 * @author ({{author}})
 */
class {{model_name}} extends {{parent_class}}

{

	/**
	 * 数据表前缀
	 * @var string
	 */
	protected static $db_pre = '{{suff}}';


	{% for field in fields %}
	{{field}}
	{% endfor %}


}
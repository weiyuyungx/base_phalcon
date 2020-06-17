<h4>这是对phalcon二次封装</h4>
<h6>有以下特点</h6>
<ul>
<li>数据处理严格分层</li>
<li>二次封装了数据查询DAO，以及MODEL事件操作</li>
<li>用助手工具可以一键生成Model</li>
<li>访问权限与业务操作分开</li>
<li>简单明了，上手可用，快速开发，低耦合</li>
</ul>           

<h6>二期功能</h6>
<ul>
<li>根据数据表外键，自动生成Model关系</li>
<li>开发环境与正式环境的配置参数分离</li>
</ul>           

<h6>大概结构图:</h6>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;权限Controller(用户，管理员，会员等)<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|(继承获取权限)<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|<br/>
入口----> 业务Controller ------> Service(业务封装)--->DAO（数据查询）---->Model(数据表示)


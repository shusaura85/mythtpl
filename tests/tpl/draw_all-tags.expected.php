<?php namespace MythTPL\Template; if(!class_exists('MythTPL\MythTPL')){exit;}?><!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $title; ?></title>
</head>
<body>

    

    
    
	
	<h1>Test Myth TPL <?php echo TEST_CONSTANT_DEFINE; ?></h1>
	<hr>

	<h2>Variables</h2>
	Variable: <?php echo $variable; ?> <br><br>
	Init Variable <?php $v = 10; ?> <br><br>
	Show Variable <?php echo $v; ?> <br><br>
	Modifier <?php echo strlen($variable); ?> <br><br>
	Cascade Modifier <?php echo strlen(substr($variable,2,5)); ?> <br><br>
	Scoping (object) <?php echo $user->name; ?> <br><br>
	Scoping (array) <?php echo $week["0"]; ?> <br><br>
	Variable as key <?php echo $week[$numbers["0"]]; ?> <br><br>
	Var test <?php echo $variable; ?> <br><br>
	<hr />
	String test <?php echo date('Y-m-d', 4108722722); ?> <br><br>
	<?php if( $num1 == 10 || $num1 == 20 ){ ?>

		num1 is in wanted values<br />
	<?php } ?>

	String test 2 <?php echo date("Y-m-d H:i:s",4108722722); ?> <br><br>
	String test 2 <?php echo "Y-m-d H:i:s"; ?> <br><br>
	<?php $counter1=-1;  if( ($week !== null) && ( is_array($week) || $week instanceof Traversable ) && sizeof($week) ) foreach( $week as $key1 => $value1 ){ $counter1++; ?><a href="#<?php echo $counter1; ?>"><?php echo $variable; ?></a><br><?php } ?>

	<hr />

    <h2>Ternary Operator</h2>
    The title is: <?php echo (isset($title)?"$title":'default title'); ?>



	<h2>Loop</h2>
	Simple Loop
	<ul>
		<?php $counter1=-1;  if( ($week !== null) && ( is_array($week) || $week instanceof Traversable ) && sizeof($week) ) foreach( $week as $key1 => $value1 ){ $counter1++; ?>

		<li>
			<?php echo $key1; ?> <?php echo $value1; ?>

		</li>
		<?php } ?>

	</ul><br><br>

	Modifier on Loop
	<ul>
		<?php $counter1=-1; $newvar1=array_reverse($week); if( ($newvar1 !== null) && ( is_array($newvar1) || $newvar1 instanceof Traversable ) && sizeof($newvar1) ) foreach( $newvar1 as $key1 => $i ){ $counter1++; ?>

		<li><?php echo $i; ?></li>
		<?php } ?>

	</ul><br><br>

	Simple Nested Loop
	<ul>
		<?php $counter1=-1;  if( ($table !== null) && ( is_array($table) || $table instanceof Traversable ) && sizeof($table) ) foreach( $table as $key1 => $value1 ){ $counter1++; ?>

		<li>
			<?php $counter2=-1;  if( ($value1 !== null) && ( is_array($value1) || $value1 instanceof Traversable ) && sizeof($value1) ) foreach( $value1 as $key2 => $value2 ){ $counter2++; ?>

			<?php echo $value2; ?>,
			<?php } ?>

		</li>
		<?php } ?>

	</ul><br><br>

	Loop on created array
	<ul>
		<?php $counter1=-1; $newvar1=range(5,10); if( ($newvar1 !== null) && ( is_array($newvar1) || $newvar1 instanceof Traversable ) && sizeof($newvar1) ) foreach( $newvar1 as $key1 => $i ){ $counter1++; ?>

		<li><?php echo $i; ?></li>
		<?php } ?>

	</ul><br><br>
	
	Loop on empty array
	<ul>
		<?php $counter1=-1;  if( ($empty_array !== null) && ( is_array($empty_array) || $empty_array instanceof Traversable ) && sizeof($empty_array) ) foreach( $empty_array as $key1 => $value1 ){ $counter1++; ?>

		<li><?php echo $i; ?></li>
		<?php }else{ ?>

		<li>This is an empty array</li>
		<?php } ?>

	</ul><br><br>

	<h2>If</h2>
	True condition: <?php if( true ){ ?>This is true<?php } ?> <br><br>
	Modifier inside if: <?php if( is_string($variable) ){ ?>True<?php } ?> <br><br>


	<h2>Function test</h2>
	Function with parameters: <?php echo date('d-m-Y', 4108722722); ?> <br><br>

	<h2>Escape Text</h2>
	Malicious content: <?php echo htmlspecialchars( $bad_text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8', FALSE ); ?> <br><br>

	<h2>Custom tag</h2>
	{@message to translate@} <br><br>

	<h2>Custom tag 2</h2>
	{%message to translate|english%} <br><br>
        
	<h2>Escape variable with autoescape</h2>
	<?php echo htmlspecialchars( $bad_variable, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8', FALSE ); ?>

	<?php echo $safe_variable; ?>

        
	<h2>Escape variable with modifiers</h2>
	<?php echo escape($bad_variable); ?> <br><br>
	
	<h2>More modifier examples</h2>
	<?php echo escape(ucwords(strtolower('this IS a STring I╲⟍⎝╲༼◕ ◕ ༽╱⎠⟋╱I')),"UTF-8"); ?><br>
	
	
	<h2>{php} Tag status: <?php echo ($this->getTagPhp() ? 'enabled' : 'disabled'); ?></h2>
	<?php /* {php} tag detected but not enabled */   /* {/php} tag detected but not enabled */ ?>



</body>
</html>
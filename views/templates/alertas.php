<?php 
    foreach($alertas as $key => $alerta): 
        foreach($alerta as $mensaje):
?>
            <p class="alerta <?php echo $key; ?>"><?php echo $mensaje; ?></p>
<?php 
        endforeach;
    endforeach; 
?>
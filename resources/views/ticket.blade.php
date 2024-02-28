<script>
	const venta = `@php echo($venta); @endphp`;
	try{
		let w = window.open("","_self");
		w.document.write(venta);
		w.window.print();
		w.document.close();
		result = true;    
	}catch(err) {
		alert('Pago guardado, error en impresión de ticket');
	}
</script>
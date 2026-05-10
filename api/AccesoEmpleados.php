










<html>
	<head>
		<title>Identificación</title>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<script type="text/javascript">
window.name = "NV_1778429433309";

function comprobarclave()
{
	if(document.forms[0]['USUARIO'].value=="")
	{
		alert("El campo 'Usuario' es obligatorio");
	}
	else if(document.forms[0]['CLAVE'].value=="")
	{
		alert("El campo 'Clave' es obligatorio");
	}
	else
	{
		document.forms[0]['CLAVECIFRADA'].value = cifrar(document.forms[0]['CLAVE'].value);
		document.forms[0]['CLAVE'].value = "";
		document.forms[0].submit();
	}
}
</script>
<style>
.morado {
	background-color: #9A6289
}

.blanco {
	background-color: #FFFFFF
}

.lila {
	background-color: #BE9BB4
}

.moradoclaro {
	background-color: #DBC6D4
}

.verdeagua {
	background-color: #B7DDC8
}

.verde {
	background-color: #B3D76B
}

.naranja {
	background-color: #EAB863
}

.botones {
	background-color: #FFFFFF;
	padding-top: 2px;
	padding-right: 2px;
	padding-bottom: 2px;
	padding-left: 2px;
	border: 1px #6B4560 solid;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 8pt;
	font-weight: normal;
	color: #000000;
	width: 100px;
	text-decoration: none
}

.botones:hover {
	background-color: #DDCAD8;
	padding-top: 2px;
	padding-right: 2px;
	padding-bottom: 2px;
	padding-left: 2px;
	border: 1px #6B4560 solid;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 8pt;
	font-weight: normal;
	color: #5A3A51;
	text-decoration: none;
}

input {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 8pt;
	font-weight: bold;
	color: #573957;
	text-decoration: none;
	background-color: #DBC6D4;
	border: #FFFFFF;
	border-style: solid;
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px
}

.input_check {
	background-color: transparent;
	border: none;
}

.usuario {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 9pt;
	font-weight: bold;
	color: #FFFFFF;
	text-decoration: none
}

.usuarioMostrar {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 9pt;
	font-weight: bold;
	color: #B3D76B;
	text-decoration: none
}

.mensaje {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12pt;
	font-weight: bold;
	color: #E8FF48;
	text-decoration: none
}

.botones2 {
	background-color: #F0AA94;
	padding-top: 2px;
	padding-right: 2px;
	padding-bottom: 2px;
	padding-left: 2px;
	text-align: center;
	border: 1px #E77551 solid;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 8pt;
	font-weight: bold;
	color: #903214;
	width: 100px;
	text-decoration: none
}

.botones2:hover {
	background-color: #B3D76B;
	padding-top: 2px;
	padding-right: 2px;
	padding-bottom: 2px;
	padding-left: 2px;
	border: 1px solid;
	text-align: center;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 8pt;
	font-weight: bold;
	color: #000000;
	text-decoration: none;
	border-color: #799F2B #669933 #669933
}

.titulo {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	font-weight: bold;
	color: #FFFFFF;
	text-decoration: none;
	background-color: #9A6289;
	width: 100%;
	padding-left: 10px
}

.txtblanco {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	text-align: justify;
	font-weight: normal;
	color: #FFFFFF;
	text-decoration: none;
	padding-top: 4px;
	padding-right: 4px;
	padding-bottom: 4px;
	padding-left: 10px
}

.txtpositivo {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	font-weight: bold;
	color: #FFFFFF;
	text-decoration: none;
	padding-top: 4px;
	padding-right: 4px;
	padding-bottom: 4px;
	padding-left: 10px
}

.txtidentificacion {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	font-weight: bold;
	color: #DBC6D4;
	text-decoration: none;
	padding-top: 4px;
	padding-right: 4px;
	padding-bottom: 4px;
	padding-left: 10px
}

.txtmensajeerror {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	font-weight: normal;
	color: #FF9900;
	text-decoration: none;
	padding-top: 4px;
	padding-right: 4px;
	padding-bottom: 4px;
	padding-left: 10px;
	text-align: justify;
}

.lista {
	background-image: url(secretaria_on.gif);
	background-repeat: no-repeat;
	background-position: 5px 5px
}

.txtcabecera {
	font-family: impact, Arial, Helvetica, sans-serif;
	background-color: #8D5A7E;
	color: #FFFFFF;
	font-size: 18pt
}

.blanco a {
	color: #B3D76B;
	font-family: tahoma;
	font-size: 14pt;
	text-decoration: none;
}

.txtmensajeerror
{
	color:#FF9900;
	font-family:Arial,Helvetica,sans-serif;
	font-size:10pt;
	font-weight:normal;
	padding:4px 4px 4px 10px;
	text-align:justify;
	text-decoration:none;
}

</style>
</head>
<body leftmargin="0" topmargin="0" bgcolor="#ffffff" marginheight="0"
	marginwidth="0">
<table border="0" cellpadding="0" cellspacing="0" height="100%"
	width="100%">
	<tbody>
		<tr>
			<td colspan="2" width="60%">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><img src="../images/puertaTrasera/logo_rayuela.gif"
							height="157" width="230"></td>
						<td align="right" height="165" valign="bottom">&nbsp;</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td class="morado" height="165" valign="top" width="30%">
			<table align="center" border="0" cellpadding="0" cellspacing="5"
				width="90%">
				<tbody>
					<tr>
						<td>
						
						<form name="FORMULARIO_IDEN_USU" method="POST" action="ComprobarUsuario.jsp">
						<table align="center" border="0" cellpadding="0" cellspacing="0">
							<tbody>
								<tr align="center">
									<td class="usuario" height="31" valign="middle" width="75%">
											Usuario <br>
											<input name="usuario" value="" size="20" type="text">
									</td>
								</tr>
								<tr align="center">
									<td class="usuario" width="75%"><br>
										Contraseña<br>
										<input name="password" size="20" type="password">
									</td>
								</tr>
								<tr>
									<td width="75%">
										&nbsp;
									</td>
								</tr>
								<tr>
									<td width="75%">&nbsp;</td>
								</tr>
								<tr align="center">
									<td width="75%">
									<table border="0" cellpadding="0" cellspacing="0">
										<tbody>
											<tr>
												<td>
												<div align="center"><a href="javascript:comprobarclave();"
													class="botones2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Entrar&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></div>
												</td>
											</tr>
										</tbody>
									</table>
									</td>
								</tr>
							</tbody>
						</table>
						</form>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td valign="middle" width="35%">

			<table border="0" cellpadding="0" cellspacing="0" height="151"
				width="100%">
				<tbody>
					<tr align="center">
						<td>&nbsp;</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="lila" height="100" width="60%">

			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr align="center">
						<td width="20%">&nbsp;</td>
						<td width="20%">&nbsp;</td>
						<td width="20%"><img
							src="../images/puertaTrasera/menu_GESTION_CENTROS.gif"
							height="69" width="103"></td>
						<td width="20%">&nbsp;</td>
						<td width="20%">&nbsp;</td>
					</tr>
				</tbody>
			</table>

			</td>
			<td class="moradoclaro" align="right" height="100" valign="bottom"
				width="30%"><img src="../images/puertaTrasera/txt_ray.gif"
				height="36" width="167"></td>
			<td class="lila" align="left" height="100" valign="bottom"
				width="10%"><img src="../images/puertaTrasera/txt_uela.gif"
				height="36" width="166"></td>
		</tr>
		<tr>
			<td colspan="2" align="right" height="19" valign="top" width="60%">&nbsp;</td>
			<td rowspan="2" class="verdeagua" align="right" height="63"
				valign="bottom" width="30%"><img
				src="../images/puertaTrasera/tit_plataforma.gif" height="32"
				width="167"></td>
			<td rowspan="2" class="verde" align="right" height="63"
				valign="bottom" width="10%"><img
				src="../images/puertaTrasera/tit_educativa.gif" height="32"
				width="166"></td>
		</tr>
		<tr>
			<td align="right" height="32" valign="top">&nbsp;</td>
			<td align="right" valign="top">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" rowspan="2" align="right" valign="top" width="60%">
			<table width="100%">
				<tbody>
					<tr align="center">
						<td valign="top">&nbsp;</td>
						<td valign="top">&nbsp;</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td class="naranja" width="30%">&nbsp;</td>
			<td align="right" height="21" valign="top" width="10%">
				<img src="../images/puertaTrasera/tit_extrem.gif" height="17" width="166">
			</td>
		</tr>
		<tr>
			<td class="naranja" width="30%">&nbsp;</td>
			<td align="center" valign="middle" width="10%">
				<img src="../images/puertaTrasera/logo_junta.gif" height="59" width="166"><br>
				<img src="../images/puertaTrasera/FEDER-UE.jpg" height="44" width="165">
			</td>
		</tr>
	</tbody>
</table>
</body>
</html>

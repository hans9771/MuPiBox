<?php
	$onlinejson = file_get_contents('https://raw.githubusercontent.com/splitti/MuPiBox/main/version.json');
	$dataonline = json_decode($onlinejson, true);
	include ('includes/header.php');

	$change=0;
	$CHANGE_TXT="<div id='lbinfo'><ul id='lbinfo'>";

	if( $_POST['change_netboot'] == "activate for next boot" )
		{
		$command = "sudo /boot/dietpi/func/dietpi-set_software boot_wait_for_network 1";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>Wait for Network on boot is enabled</li>";
		}
	else if( $_POST['change_netboot'] == "disable" )
		{
		$command = "sudo /boot/dietpi/func/dietpi-set_software boot_wait_for_network 0";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>Wait for Network on boot is disabled</li>";
		}

	if( $_POST['change_wifi'] == "disable" )
		{
		$command = "echo 'dtoverlay=disable-wifi' | sudo tee -a /boot/config.txt";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>OnBoard Wifi disabled [restart necessary]</li>";
		}
	else if( $_POST['change_wifi'] == "enable" )
		{
		$command = "sudo sed -i -e 's/dtoverlay=disable-wifi//g' /boot/config.txt && sudo head -n -1 /boot/config.txt > /tmp/config.txt && sudo mv /tmp/config.txt /boot/config.txt";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>OnBoard Wifi enabled [restart necessary]</li>";
		}

	if( $_POST['change_turbo'] == "disable" )
		{
		$command = "sudo su - dietpi -c \". /boot/dietpi/func/dietpi-globals && G_SUDO G_CONFIG_INJECT 'initial_turbo' 'initial_turbo=0' /boot/config.txt\"";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>Inital Turbo disabled</li>";
		}
	else if( $_POST['change_turbo'] == "enable" )
		{
		$command = "sudo su - dietpi -c \". /boot/dietpi/func/dietpi-globals && G_SUDO G_CONFIG_INJECT 'initial_turbo' 'initial_turbo=30' /boot/config.txt\"";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>Inital Turbo enabled</li>";
		}

	if( $_POST['change_swap'] == "disable" )
		{
		$command = "sudo /boot/dietpi/func/dietpi-set_swapfile 0";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>SWAP disabled</li>";
		}
	else if( $_POST['change_swap'] == "enable" )
		{
		$command = "sudo /boot/dietpi/func/dietpi-set_swapfile 1";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>SWAP enabled</li>";
		}

	if ($_POST['change_cpug'])
		{
		$command = "sudo su - dietpi -c \". /boot/dietpi/func/dietpi-globals && G_SUDO G_CONFIG_INJECT 'CONFIG_CPU_GOVERNOR=' 'CONFIG_CPU_GOVERNOR=".$_POST['cpugovernor']."' /boot/dietpi.txt\"";
		$test=exec($command, $output, $result );
		$command = "sudo /boot/dietpi/func/dietpi-set_cpu";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>CPU Governor changet to  ".$_POST['cpugovernor']."</li>";
		}

	if( $_POST['change_sd'] == "activate for next boot" )
		{
		$command = "echo 'dtoverlay=sdtweak,overclock_50=100' | sudo tee -a /boot/config.txt";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>SD Overclocking activated [restart necessary]</li>";
		}
	else if( $_POST['change_sd'] == "disable" )
		{
		$command = "sudo sed -i -e 's/dtoverlay=sdtweak,overclock_50=100//g' /boot/config.txt && sudo head -n -1 /boot/config.txt > /tmp/config.txt && sudo mv /tmp/config.txt /boot/config.txt";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>SD Overclocking disabled [restart necessary]</li>";
		}

	$command = "sudo bash -c \"[[ ! -f '/etc/systemd/system/dietpi-postboot.service.d/dietpi.conf' ]] || echo 1\"";
	exec($command, $netbootoutput, $netbootresult );

	if( $netbootoutput[0] )
		{
		$netboot_state = "active";
		$change_netboot = "disable";
		}
	else
		{
		$netboot_state = "disabled";
		$change_netboot = "activate for next boot";
		}

	$command = "sudo /usr/bin/cat /boot/config.txt | /usr/bin/grep 'dtoverlay=sdtweak,overclock_50=100'";
	exec($command, $sdoutput, $sdresult );

	if( $sdoutput[0] )
		{
		$sd_state = "active";
		$change_sd = "disable";
		}
	else
		{
		$sd_state = "disabled";
		$change_sd = "activate for next boot";
		}

	if( $_POST['change_vnc'] == "disable" )
		{
		exec("sudo apt-get remove x11vnc websockify");
		exec("sudo rm -R /usr/share/novnc");
		exec("sudo systemctl stop mupi_vnc.service");
		exec("sudo systemctl stop mupi_novnc.service");
		exec("sudo systemctl disable mupi_vnc.service");
		exec("sudo systemctl disable mupi_novnc.service");
		exec("sudo su - -c \"/usr/bin/cat <<< $(/usr/bin/jq --arg v \"0\" '.tweaks.vnc = $v' /etc/mupibox/mupiboxconfig.json) >  /etc/mupibox/mupiboxconfig.json\"");
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>VNC-Services disabled</li>";
		}
	else if( $_POST['change_vnc'] == "enable" )
		{
		exec("sudo apt-get install x11vnc websockify");
		exec("sudo git clone https://github.com/novnc/noVNC.git /usr/share/novnc");
		exec("sudo chown -R dietpi:dietpi /usr/share/novnc");
		exec("sudo systemctl enable mupi_vnc.service");
		exec("sudo systemctl enable mupi_novnc.service");
		exec("sudo systemctl start mupi_vnc.service");
		exec("sudo systemctl start mupi_novnc.service");		
		exec("sudo su - -c \"/usr/bin/cat <<< $(/usr/bin/jq --arg v \"1\" '.tweaks.vnc = $v' /etc/mupibox/mupiboxconfig.json) >  /etc/mupibox/mupiboxconfig.json\"");
		
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>VNC-Services activated</li>";
		}

	$CHANGE_TXT=$CHANGE_TXT."</ul>";
?>

<form class="appnitro"  method="post" action="tweaks.php" id="form">
	<div class="description">
		<h2>MupiBox tweaks</h2>
		<p>Make your box smarter and faster...</p>
	</div>
		<ul >

		<li class="li_1"><h2>Overclock SD Card</h2>
			<p>
			Just for highspeed SD Cards. You can damage data or the microSD itself!
			</p>
			<p>
			<?php
			echo "Overclocking state: <b>".$sd_state."</b>";
			?>
			</p>
			<input id="saveForm" class="button_text" type="submit" name="change_sd" value="<?php print $change_sd; ?>" />
		</li>

		<li class="li_1"><h2>Wait for Network on boot</h2>
			<p>
			Speeds up the boot time, but sometimes the boot process is to fast and you have to wait for the network to be ready... Try it, if disabling this option works for you!
			</p>
			<p>
			<?php
			echo "Wait for Network on boot: <b>".$netboot_state."</b>";
			?>
			</p>
			<input id="saveForm" class="button_text" type="submit" name="change_netboot" value="<?php print $change_netboot; ?>" />
		</li>

		<li class="li_1"><h2>Initial Turbo</h2>
			<p>
			Initial Turbo avoids throtteling sometimes...
			</p>
			<p>
			<?php
			$command = "cat /boot/config.txt | grep initial_turbo | cut -d '=' -f 2";
			$turbo = exec($command, $output);
			echo "Turbo seconds: <b>".$turbo."</b>";
			if($turbo == 0)
				{
				$change_turbo="enable";
				}
			else
				{
				$change_turbo="disable";
				}

			?>
			</p>
			<input id="saveForm" class="button_text" type="submit" name="change_turbo" value="<?php print $change_turbo; ?>" />
		</li>

		<li class="li_1"><h2>CPU Governor</h2>
			<p>
			Try powersave (Limits CPU frequency to 600 MHz - Helps to avoid throtteling).
			</p>
			<p>
			<div>
			<select id="cpugovernor" name="cpugovernor" class="element text medium">
			<?php
			$command = "cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_available_governors";
			$governors = exec($command, $output);
			$cpug = explode(" ", $governors);
			$command = "cat /boot/dietpi.txt | grep CONFIG_CPU_GOVERNOR | cut -d '=' -f 2";
			$current_governor = exec($command, $output);

			foreach($cpug as $key) {
			if( $key == $current_governor )
				{
				$selected = " selected=\"selected\"";
				}
			else
				{
				$selected = "";
				}
			print "<option value=\"". $key . "\"" . $selected  . ">" . $key . "</option>";
			}
			?>
			"</select>
			</div>
			</p>
			<input id="saveForm" class="button_text" type="submit" name="change_cpug" value="Save CPU Governor" />
		</li>

		<li class="li_1"><h2>SWAP</h2>
			<p>
			Enables or disables SWAP!
			</p>
			<p>
			<?php
			$command = "cat /boot/dietpi.txt | grep AUTO_SETUP_SWAPFILE_SIZE= | cut -d '=' -f 2";
			$currentswapsize = exec($command, $output);
			if($currentswapsize == 0)
				{
				$change_swap="enable";
				}
			else
				{
				$change_swap="disable";
				}

			echo "SWAP Size: <b>".$currentswapsize." MB</b>";
			?>
			</p>
			<input id="saveForm" class="button_text" type="submit" name="change_swap" value="<?php print $change_swap; ?>" />
		</li>

		<li class="li_1"><h2>Enable/Disable OnBoad Wifi</h2>
			<p>
			Enables or disables OnBoard Wifi! Please be sure what you do!
			</p>
			<p>
			<?php
			$command = "cat /boot/config.txt | grep 'dtoverlay=disable-wifi'";
			$wifionoff = exec($command, $output);
			if($wifionoff == "")
				{
				$change_wifi="disable";
				}
			else
				{
				$change_wifi="enable";
				}
			?>
			</p>
			<input id="saveForm" class="button_text" type="submit" name="change_wifi" value="<?php print $change_wifi; ?>" />
		</li>
		<li class="li_1"><h2>Enable/Disable VNC</h2>
			<p>
			Enables or disables OnBoard Wifi! Please be sure what you do!
			</p>
			<p>
			<?php
			$command = "/usr/bin/jq -r .tweaks.vnc /etc/mupibox/mupiboxconfig.json";
			$vncoff = exec($command, $output);
			if($vncoff == "1")
				{
				$change_vnc="disable";
				}
			else
				{
				$change_vnc="enable";
				}
			?>
			</p>
			<input id="saveForm" class="button_text" type="submit" name="change_vnc" value="<?php print $change_vnc; ?>" />
		</li>
	</ul>
</form>
<?php
	include ('includes/footer.php');
?>

<?php
	$change=0;
	$CHANGE_TXT="<div id='lbinfo'><ul id='lbinfo'>";
	include ('includes/header.php');

	$hdmi_rotate_option[0][0]="0";
	$hdmi_rotate_option[0][1]="Disabled (default)";
	$hdmi_rotate_option[1][0]="1";
	$hdmi_rotate_option[1][1]="90 degrees";
	$hdmi_rotate_option[2][0]="2";
	$hdmi_rotate_option[2][1]="180 degrees";
	$hdmi_rotate_option[3][0]="3";
	$hdmi_rotate_option[3][1]="270 degrees";
	$hdmi_rotate_option[4][0]="0x10000";
	$hdmi_rotate_option[4][1]="Horizontal flip";
	$hdmi_rotate_option[5][0]="0x20000";
	$hdmi_rotate_option[5][1]="Vertical flip";
	$lcd_rotate_option[0][0]="0";
	$lcd_rotate_option[0][1]="Disabled (default)";
	$lcd_rotate_option[1][0]="2";
	$lcd_rotate_option[1][1]="180 degrees";
	$dlcd_rotate_option[0][0]="0";
	$dlcd_rotate_option[0][1]="Disabled (default)";
	$dlcd_rotate_option[1][0]="2";
	$dlcd_rotate_option[1][1]="180 degrees";

	$lcd_rotation_state=`sed -n '/^[[:blank:]]*lcd_rotate=/{s/^[^=]*=//p;q}' /boot/config.txt`;
	$dlcd_rotation_state=`sed -n '/^[[:blank:]]*display_lcd_rotate=/{s/^[^=]*=//p;q}' /boot/config.txt`;
	$hdmi_rotation_state=`sed -n '/^[[:blank:]]*display_hdmi_rotate=/{s/^[^=]*=//p;q}' /boot/config.txt`;

	if(isset($_POST['hdmi_rotation']) && $_POST['hdmi_rotation'] != substr($hdmi_rotation_state,0,-1))
		{
		exec("sudo su - dietpi -c \". /boot/dietpi/func/dietpi-globals && G_SUDO G_CONFIG_INJECT 'display_hdmi_rotate=' 'display_hdmi_rotate=" . $_POST['hdmi_rotation'] . "' /boot/config.txt\"");
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>Set HDMI-Rotation [reboot is necessary]</li>";
		}
	if(isset($_POST['lcd_rotation']) && $_POST['lcd_rotation'] != substr($lcd_rotation_state,0,-1))
		{
		exec("sudo su - dietpi -c \". /boot/dietpi/func/dietpi-globals && G_SUDO G_CONFIG_INJECT 'lcd_rotate=' 'lcd_rotate=" . $_POST['lcd_rotation'] . "' /boot/config.txt\"");
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>Set LCD-Rotation [reboot is necessary]</li>";
		}
	if(isset($_POST['dlcd_rotation']) && $_POST['dlcd_rotation'] != substr($dlcd_rotation_state,0,-1))
		{
		exec("sudo su - dietpi -c \". /boot/dietpi/func/dietpi-globals && G_SUDO G_CONFIG_INJECT 'display_lcd_rotate=' 'display_lcd_rotate=" . $_POST['dlcd_rotation'] . "' /boot/config.txt\"");
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>Set Display-LCD-Rotation [reboot is necessary]</li>";
		}

	if($_POST['stop_sleeptimer'] == "Stop running timer")
		{
		$command = "sudo pkill -f \"sleep_timer.sh\"";
		exec($command);
		$command = "sudo rm /tmp/.time2sleep";
		exec($command);
		$change=3;
		$CHANGE_TXT=$CHANGE_TXT."<li>Sleeptimer stopped</li>";
		}

	if($_POST['change_gpu'] == "disable")
		{
		$data["chromium"]["gpu"]=false;	
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>GPU-Support disabled</li>";
		}
	if($_POST['change_gpu'] == "enable")
		{
		$data["chromium"]["gpu"]=true;	
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>GPU-Support activated</li>";
		}

	if($_POST['change_pm2log'])
		{
		if($data["pm2"]["ramlog"])
			{
			exec("sudo bash -c \"sed '/\/home\/dietpi\/.pm2\/logs/d' /etc/fstab > /tmp/.fstab && mv /tmp/.fstab /etc/fstab\"");
			$pm2_state = "disabled";
			$change_pm2 = "enable";
			$data["pm2"]["ramlog"]=0;
			}
		else
			{
			exec("sudo bash -c \"sed '/^tmpfs \/var\/log.*/a tmpfs \/home\/dietpi\/.pm2\/logs tmpfs size=50M,noatime,lazytime,nodev,nosuid,mode=1777' /etc/fstab > /tmp/.fstab && mv /tmp/.fstab /etc/fstab\"");
			$pm2_state = "active";
			$change_pm2 = "disable";
			$data["pm2"]["ramlog"]=1;
			}
		$change=2;
		$CHANGE_TXT=$CHANGE_TXT."<li>PM2-Logs to RAM ".$pm2_state." on next boot</li>";
		}
	else
		{
		if($data["pm2"]["ramlog"])
			{
				$pm2_state = "active";
				$change_pm2 = "disable";
			}
		else
			{
				$pm2_state = "disabled";
				$change_pm2 = "enable";
			}
		}
			
	if($_POST['potimer'])
		{
		$timerSleepingTime=$_POST['powerofftimer']*60;
		$command = "sudo nohup /usr/local/bin/mupibox/./sleep_timer.sh ".$timerSleepingTime."  > /dev/null 2>&1 &";
		exec($command);
		$change=3;
		$CHANGE_TXT=$CHANGE_TXT."<li>".$_POST['powerofftimer']." minutes sleeptimer started</li>";
		//sudo pkill -f "sleep_timer.sh"
		}

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

	if( $_POST['change_warnings'] == "disable" )
		{
		$command = "sudo sed -i -e 's/avoid_warnings=1//g' /boot/config.txt && sudo head -n -1 /boot/config.txt > /tmp/config.txt && sudo mv /tmp/config.txt /boot/config.txt";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>Warning Icons disabled [restart necessary]</li>";
		}
	else if( $_POST['change_warnings'] == "enable" )
		{
		$command = "echo 'avoid_warnings=1' | sudo tee -a /boot/config.txt";
		exec($command, $output, $result );
		$change=1;
		$CHANGE_TXT=$CHANGE_TXT."<li>Warning Icons enabled [restart necessary]</li>";
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
		
 if( $_POST['audioset'] )
  {
	$tvcommand = "sudo su dietpi -c '/usr/bin/amixer sget Master | grep \"Right:\" | cut -d\" \" -f7 | sed \"s/\\[//g\" | sed \"s/\\]//g\" | sed \"s/\%//g\"'";
	$tvresult = exec($tvcommand, $tvoutput);
	if($_POST['thisvolume'] != $tvoutput[0])
		{ 
		$command="sudo su dietpi -c '/usr/bin/amixer sset Master " . $_POST['thisvolume'] . "%'";
		$set_volume = exec($command, $output );
		$CHANGE_TXT=$CHANGE_TXT."<li>Volume: " . $_POST['thisvolume'] . "%</li>";
		$change=2;
		}
  }
 if( $_POST['displayset'] )
  {
	$command = "cat /sys/class/backlight/rpi_backlight/brightness";
	$thisbrightness = exec($command, $tboutput);

	switch ($_POST['newbrightness']) {
    case "0":
        $new_bn=0;
        break;
    case "20":
        $new_bn=51;
        break;
    case "40":
        $new_bn=102;
        break;
    case "60":
        $new_bn=153;
        break;
    case "80":
        $new_bn=204;
        break;
    case "100":
        $new_bn=255;
        break;
	default:
        $new_bn=255;
	}

	if( $new_bn != $tboutput[0] )
		{
		$brcommand="sudo su - -c 'echo " . $new_bn . " > /sys/class/backlight/rpi_backlight/brightness'";
		$set_brightness = exec($brcommand, $broutput );
		$CHANGE_TXT=$CHANGE_TXT."<li>Backlight-Brightness: " . $_POST['newbrightness'] . "%</li>";
		$change=2;
		}
  }
 if( $data["mupibox"]["physicalDevice"]!=$_POST['audio'] && $_POST['audioset'])
  {
  $data["mupibox"]["physicalDevice"]=$_POST['audio'];
  if( $_POST['audio'] == "mupihat" )
	{
	$command = "sudo /boot/dietpi/func/dietpi-set_hardware soundcard 'hifiberry-dac'";
	$change_soundcard = exec($command, $output, $change_soundcard );
	$command = <<<'EOD'
sudo sed -zi '/#--------MuPiHAT--------/!s/$/\n#--------MuPiHAT--------/' /boot/config.txt
EOD;
	exec($command);
	$command = <<<'EOD'
sudo sed -zi '/dtparam=i2c_arm=on/!s/$/\ndtparam=i2c_arm=on/' /boot/config.txt
EOD;
	exec($command);
	$command = <<<'EOD'
sudo sed -zi '/dtparam=i2c1=on/!s/$/\ndtparam=i2c1=on/' /boot/config.txt
EOD;
	exec($command);
	$command = <<<'EOD'
sudo sed -zi '/dtparam=i2c_arm_baudrate=50000/!s/$/\ndtparam=i2c_arm_baudrate=50000/' /boot/config.txt
EOD;
	exec($command);
	$command = <<<'EOD'
sudo sed -zi '/dtoverlay=max98357a,sdmode-pin=16/!s/$/\ndtoverlay=max98357a,sdmode-pin=16/' /boot/config.txt
EOD;
	exec($command);
	$command = <<<'EOD'
sudo sed -zi '/dtoverlay=i2s-mmap/!s/$/\ndtoverlay=i2s-mmap/' /boot/config.txt
EOD;
	exec($command);
	$CHANGE_TXT=$CHANGE_TXT."<li>Soundcard changed to  MuPiHat</li>";
	}
  else
	{
	$command = <<<'EOD'
sudo sed -i '/#--------MuPiHAT--------/d' /boot/config.txt
EOD;
	exec($command);
	$command = <<<'EOD'
sudo sed -i '/dtparam=i2c_arm=on/d' /boot/config.txt
EOD;
	exec($command);
	$command = <<<'EOD'
sudo sed -i '/dtparam=i2c1=on/d' /boot/config.txt
EOD;
	exec($command);
	$command = <<<'EOD'
sudo sed -i '/dtparam=i2c_arm_baudrate=50000/d' /boot/config.txt
EOD;
	exec($command);
	$command = <<<'EOD'
sudo sed -i '/dtoverlay=max98357a,sdmode-pin=16/d' /boot/config.txt
EOD;
	exec($command);
	$command = <<<'EOD'
sudo sed -i '/dtoverlay=i2s-mmap/d' /boot/config.txt
EOD;
	exec($command);

	$command = "sudo /boot/dietpi/func/dietpi-set_hardware soundcard '" . $_POST['audio'] . "'";
	$change_soundcard = exec($command, $output, $change_soundcard );
	
	$CHANGE_TXT=$CHANGE_TXT."<li>Soundcard changed to  ".$data["mupibox"]["physicalDevice"]."x</li>";
	}
  $change=2;
  }
 if( $data["mupibox"]["host"]!=$_POST['hostname'] && $_POST['submithn'])
  {
  $data["mupibox"]["host"]=$_POST['hostname'];
  $command = "sudo /boot/dietpi/func/change_hostname " . $_POST['hostname'];
  $change_hostname = exec($command, $output, $change_hostname );
  $command = "sudo su dietpi -c '/usr/local/bin/mupibox/./set_hostname.sh'";
  exec($command);
  $CHANGE_TXT=$CHANGE_TXT."<li>Hostname changed to  ".$data["mupibox"]["host"]." [reboot is necessary]</li>";
  $change=1;
  }
 if( $_POST['theme'] != $data["mupibox"]["theme"] && $_POST['mupiset'] )
  {
  $data["mupibox"]["theme"]=$_POST['theme'];
  $CHANGE_TXT=$CHANGE_TXT."<li>New Theme  ".$data["mupibox"]["theme"]."  is active</li>";
  $change=1;
  }
 if( $_POST['tts'] != $data["mupibox"]["ttsLanguage"] && $_POST['mupiset'] )
  {
  $data["mupibox"]["ttsLanguage"]=$_POST['tts'];
  $CHANGE_TXT=$CHANGE_TXT."<li>New TTS Language  ".$data["mupibox"]["ttsLanguage"]." [reboot is necessary]</li>";
  $command = "sudo rm /home/dietpi/MuPiBox/tts_files/*.mp3";
  exec($command, $output, $result );
  $change=2;
  }

 if( $data["shim"]["ledBrightnessMax"]!=$_POST['ledmaxbrightness'] && $_POST['powerset'] )
  {
  $data["shim"]["ledBrightnessMax"]=$_POST['ledmaxbrightness'];
  $CHANGE_TXT=$CHANGE_TXT."<li>LED standard brightness set to ".$data["shim"]["ledBrightnessMax"]."%</li>";
  $change=2;
  }

 if( $data["shim"]["ledBrightnessMin"]!=$_POST['ledminbrightness'] && $_POST['powerset'] )
  {
  $data["shim"]["ledBrightnessMin"]=$_POST['ledminbrightness'];
  $CHANGE_TXT=$CHANGE_TXT."<li>LED standard brightness set to ".$data["shim"]["ledBrightnessMin"]."%</li>";
  $change=2;
  }

 if( $data["mupibox"]["maxVolume"]!=$_POST['maxVolume'] && $_POST['audioset'] )
  {
  $data["mupibox"]["maxVolume"]=$_POST['maxVolume'];
  $CHANGE_TXT=$CHANGE_TXT."<li>Max Volume is set to ".$data["mupibox"]["maxVolume"]."% [reboot is necessary]</li>";
  $change=2;
  }

 if( $data["mupibox"]["startVolume"]!=$_POST['volume'] && $_POST['audioset'] )
  {
  $data["mupibox"]["startVolume"]=$_POST['volume'];
  $CHANGE_TXT=$CHANGE_TXT."<li>Start Volume is set to ".$data["mupibox"]["startVolume"]."%</li>";
  $change=2;
  }
 if($_POST['idletime'])
  {
  $data["timeout"]["idlePiShutdown"]=$_POST['idlePiShutdown'];
  $CHANGE_TXT=$CHANGE_TXT."<li>Idle Shutdown Time is set to ".$data["timeout"]["idlePiShutdown"]." minutes</li>";
  $change=2;
  }
 if($data["timeout"]["idleDisplayOff"]!=$_POST['idleDisplayOff'] && isset($_POST['displayset']) && $_POST['idleDisplayOff'] >= 0)
  {
  $data["timeout"]["idleDisplayOff"]=$_POST['idleDisplayOff'];
  $CHANGE_TXT=$CHANGE_TXT."<li>Idle Time for Display is set to ".$data["timeout"]["idleDisplayOff"]." minutes</li>";
  $change=2;
  }
 if( $data["timeout"]["pressDelay"]!=$_POST['pressDelay'] && $_POST['powerset'] )
  {
  $data["timeout"]["pressDelay"]=$_POST['pressDelay'];
  $CHANGE_TXT=$CHANGE_TXT."<li>Press Button delay set to ".$_POST['pressDelay']. " seconds</li>";
  $change=2;
  }
 if( $data["shim"]["ledPin"]!=$_POST['ledPin'] && $_POST['ledPin'])
  {
  $data["shim"]["ledPin"]=$_POST['ledPin'];
  $CHANGE_TXT=$CHANGE_TXT."<li>New GPIO for Power-LED set to ".$data["shim"]["ledPin"]. "  [reboot is necessary]</li>";
  $DAEMON_ARGS='DAEMON_ARGS="--gpio '.$data["shim"]["ledPin"].'"';
  exec("sudo /usr/bin/sed -i 's|DAEMON_ARGS=\".*\"|".$DAEMON_ARGS."|g' /etc/init.d/pi-blaster.boot.sh");

  $change=2;
  }
 if( $data["chromium"]["resX"]!=$_POST['resX'] && $_POST['displayset'])
  {
  $data["chromium"]["resX"]=$_POST['resX'];
  $CHANGE_TXT=$CHANGE_TXT."<li>X-Resolution set to ".$data["chromium"]["resX"]." pixel</li>";
  $change=1;
  }
 if( $data["chromium"]["resY"]!=$_POST['resY'] && $_POST['displayset'])
  {
  $data["chromium"]["resY"]=$_POST['resY'];
  $CHANGE_TXT=$CHANGE_TXT."<li>Y-Resolution set to ".$data["chromium"]["resY"]." pixel</li>";
  $change=1;
  }
  if( $data["mupibox"]["maxVolume"] < $data["mupibox"]["startVolume"] )
	{
	$data["mupibox"]["startVolume"]=$data["mupibox"]["maxVolume"];
	$CHANGE_TXT=$CHANGE_TXT."<li>Start Volume is set to ".$data["mupibox"]["maxVolume"]."% because of max volume setting</li>";
	$change=2;
	}
 if( $change == 1 )
  {
   $json_object = json_encode($data);
   $save_rc = file_put_contents('/tmp/.mupiboxconfig.json', $json_object);
   exec("sudo chmod 755 /etc/mupibox/mupiboxconfig.json");
   exec("sudo mv /tmp/.mupiboxconfig.json /etc/mupibox/mupiboxconfig.json");
   exec("sudo /usr/local/bin/mupibox/./setting_update.sh");
   exec("sudo -i -u dietpi /usr/local/bin/mupibox/./restart_kiosk.sh");
  }
 if( $change == 2 )
  {
   $json_object = json_encode($data);
   $save_rc = file_put_contents('/tmp/.mupiboxconfig.json', $json_object);
   exec("sudo mv /tmp/.mupiboxconfig.json /etc/mupibox/mupiboxconfig.json");
   exec("sudo /usr/local/bin/mupibox/./setting_update.sh");
  }
$CHANGE_TXT=$CHANGE_TXT."</ul></div>";
?>


<form class="appnitro" name="mupi" method="post" action="mupi.php" id="form">
<div class="description">
<h2>MupiBox settings</h2>
<p>This is the central configuration of your MuPiBox...</p>
</div>

	<details>
		<summary><i class="fa-solid fa-clock"></i> Timer settings</summary>
		<ul>
			<li id="li_1" >
				<h2>Sleeptimer</h2>
				<?php
				if (file_exists("/tmp/.time2sleep")) {
						?>
					MuPiBox will shut down  after (hh:mm:ss):</br>
					<div id="app"></div>
									</li>
				<li class="buttons">
				<input type="hidden" name="form_id" value="37271" />

				<input id="saveForm" class="button_text_red" type="submit" name="stop_sleeptimer" value="Stop running timer" />
			</li>

						<?php
				} else { ?>
				How many minutes to shut down MuPiBox (default 60 minutes):
				<br/>
				<div>
					<output id="rangeval" class="rangeval">60 min</output>
					<input class="range slider-progress" list="steplist_po" data-tick-step="60" name="powerofftimer" type="range" min="15" max="360" step="15.0" value="60" oninput="this.previousElementSibling.value = this.value + ' min'">
					<datalist id="steplist_po">
				<option>15</option>
				<option>30</option>
				<option>45</option>
				<option>60</option>
				<option>75</option>
				<option>90</option>
				<option>105</option>
				<option>120</option>
				<option>135</option>
				<option>150</option>
				<option>165</option>
				<option>180</option>
				<option>195</option>
				<option>210</option>
				<option>225</option>
				<option>240</option>
				<option>255</option>
				<option>270</option>
				<option>285</option>
				<option>300</option>
				<option>315</option>
				<option>330</option>
				<option>345</option>
				<option>360</option>
			</datalist>

				</div>
			</li>
			<li class="buttons">
				<input type="hidden" name="form_id" value="37271" />

				<input id="saveForm" class="button_text" type="submit" name="potimer" value="Start Power-Off Timer" />
			</li>
				<?php } ?>
			<li id="li_1" >
				<h2>Idle time to shutdown </h2>
				<p>Idle time (in minutes) without playback until the box turns off:</p>
				<div>
					<output id="rangeval" class="rangeval"><?php echo $data["timeout"]["idlePiShutdown"]; ?> min</output>
					<input class="range slider-progress" list="steplist_po" data-tick-step="60" name="idlePiShutdown" type="range" min="0" max="300" step="15.0" value="<?php echo $data["timeout"]["idlePiShutdown"]; ?>" oninput="this.previousElementSibling.value = this.value + ' min'">
				</div>

			</li>
			<li class="buttons">
				<input type="hidden" name="form_id" value="37271" />

				<input id="saveForm" class="button_text" type="submit" name="idletime" value="Submit idle time" />
			</li>
		</ul>
	</details>

	<details>
		<summary><i class="fa-solid fa-screwdriver-wrench"></i> System settings</summary>
		<ul>
			<li id="li_1" >
				<h2>Hostname </h2>
				<div>
				<input id="hostname" name="hostname" class="element text medium" type="text" maxlength="255" value="<?php
				print $data["mupibox"]["host"];
				?>"/>
				</div>
			</li>
			<li class="buttons">
				<input type="hidden" name="form_id" value="37271" />

				<input id="saveForm" class="button_text" type="submit" name="submithn" value="Submit" />
			</li>
			
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

			<li class="li_1"><h2>PM2-Logs to RAM</h2>
				<p>
				To protect the microSD, the logs can be swapped out to RAM. However, logs can no longer be evaluated after a restart.
				</p>
				<p>
				<?php
				echo "PM2-Logs to RAM: <b>".$pm2_state."</b>";
				?>
				</p>
				<input id="saveForm" class="button_text" type="submit" name="change_pm2log" value="<?php print $change_pm2; ?>" />
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

			<li class="li_1"><h2>Disable Warnings (Throtteling Warning)</h2>
				<p>
				Enables or disables the lightning icon (warning)! In worst case, this option can cause you loose all your data.
				</p>
				<p>
				<?php
				$command = "cat /boot/config.txt | grep 'avoid_warnings=1'";
				$warnings = exec($command, $output);
				if($warnings == "")
					{
					$change_warnings="enable";
					echo "Warnings: <b>disabled</b>";
					}
				else
					{
					$change_warnings="disable";
					echo "Warnings: <b>enabled</b>";
					}
				?>
				</p>
				<input id="saveForm" class="button_text" type="submit" name="change_warnings" value="<?php print $change_warnings; ?>" />
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

			<li class="li_1"><h2>Chromium-Browser-Parameters</h2>
			<h2>GPU-Support</h2>
				<p>
				Enables or disables GPU-Support! This setting is experimental.
				</p>
				<p>
				<?php
				$currentswapsize = exec($command, $output);
				if($data["chromium"]["gpu"])
					{
					$currentgpusupport="active";
					$change_gpu="disable";
					}
				else
					{
					$currentgpusupport="disabled";
					$change_gpu="enable";
					}

				echo "GPU-Support: <b>".$currentgpusupport."</b>";
				?>
				</p>
				<input id="saveForm" class="button_text" type="submit" name="change_gpu" value="<?php print $change_gpu; ?>" />
			</li>

		</ul>
	</details>

	<details>
		<summary><i class="fa-solid fa-radio"></i> MuPiBox settings</summary>
		<ul>
			<li id="li_1" >
				<h2>Theme </h2>
				<div>
				<select id="theme" name="theme" class="element text medium" onchange="switchImage();">
				<?php
				$Themes = $data["mupibox"]["installedThemes"];
				asort($Themes);
				foreach($Themes as $key) {
				if( $key == $data["mupibox"]["theme"] )
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
				</select>
				</div>
				<div class="themePrev"><img src="images/<?php print $data["mupibox"]["theme"]; ?>.png" width="250" height="150" name="selectedTheme" /></div>

			</li>
			<li id="li_1" >
				<h2>TTS Language </h2>
				<div>
				<select id="tts" name="tts" class="element text medium">
				<?php
				$language = $data["mupibox"]["googlettslanguages"];
				foreach($language as $key) {
				if( $key['iso639-1'] == $data["mupibox"]["ttsLanguage"] )
				{
				$selected = " selected=\"selected\"";
				}
				else
				{
				$selected = "";
				}
				print "<option value=\"". $key['iso639-1'] . "\"" . $selected  . ">" . $key['Language'] . "</option>";
				}
				?>
				"</select>
				</div>
			</li>
			<li class="buttons">
				<input type="hidden" name="form_id" value="37271" />

				<input id="saveForm" class="button_text" type="submit" name="mupiset" value="Submit" />
			</li>
		</ul>
	</details>


	<details>
		<summary><i class="fa-solid fa-display"></i> Display settings</summary>
		<ul>
			<li id="li_1" >
				<h2>Brightness</h2>
				<div>
					<output id="rangeval" class="rangeval"><?php 
					$tbcommand = "cat /sys/class/backlight/rpi_backlight/brightness";
					$tbrightness = exec($tbcommand, $boutput);
					switch ($boutput[0]) {
					case "0":
						$new_bn=0;
						break;
					case "51":
						$new_bn=20;
						break;
					case "102":
						$new_bn=40;
						break;
					case "153":
						$new_bn=60;
						break;
					case "204":
						$new_bn=80;
						break;
					case "255":
						$new_bn=100;
						break;
					default:
						$new_bn=100;
					}
					echo $new_bn;
				?>%</output>				
				<input class="range slider-progress" data-tick-step="20" name="newbrightness" type="range" min="0" max="100" step="20.0" value="<?php
					echo $new_bn;
				?>" oninput="this.previousElementSibling.value = this.value + '%'">
				</div>
			</li>

			<?php
				$lcd_rotation_state=`sed -n '/^[[:blank:]]*lcd_rotate=/{s/^[^=]*=//p;q}' /boot/config.txt`;
				$dlcd_rotation_state=`sed -n '/^[[:blank:]]*display_lcd_rotate=/{s/^[^=]*=//p;q}' /boot/config.txt`;
				$hdmi_rotation_state=`sed -n '/^[[:blank:]]*display_hdmi_rotate=/{s/^[^=]*=//p;q}' /boot/config.txt`;
			?>
			<li id="li_1" >
				<h2>Display Rotation Settings</h2>
				<p>These three settings can rotate the display in different constellations. A restart is necessary after saving.</p>
				<h3>HDMI-Rotation</h3>
				<div>
				<select id="hdmi_rotation" name="hdmi_rotation" class="element text medium">
				<?php
				foreach($hdmi_rotate_option as $this_option) {
					if( $this_option[0] == substr($hdmi_rotation_state, 0, -1) )
					{
					$selected = " selected=\"selected\"";
					}
					else
					{
					$selected = "";
					}
					print "<option value=\"". $this_option[0] . "\"" . $selected  . ">" . $this_option[1] . "</option>";
				}
				?>
				"</select>
				</div>
			</li>

			<li id="li_1" >
				<h3>LCD-Rotation</h3>
				<div>
				<select id="lcd_rotation" name="lcd_rotation" class="element text medium">
				<?php
				foreach( $lcd_rotate_option as $this_option ) {
					if( $this_option[0] == substr($lcd_rotation_state,0,-1) )
					{
					$selected = " selected=\"selected\"";
					}
					else
					{
					$selected = "";
					}
					print "<option value=\"". $this_option[0] . "\"" . $selected  . ">" . $this_option[1] . "</option>";
				}
				?>
				"</select>

				</div>
			</li>
			<li id="li_1" >
				<h3>Display-LCD-Rotation</h3>
				<div>
				<select id="dlcd_rotation" name="dlcd_rotation" class="element text medium">
				<?php
				foreach( $dlcd_rotate_option as $this_option ) {
					if( $this_option[0] == substr($dlcd_rotation_state,0,-1) )
					{
					$selected = " selected=\"selected\"";
					}
					else
					{
					$selected = "";
					}
					print "<option value=\"". $this_option[0] . "\"" . $selected  . ">" . $this_option[1] . "</option>";
				}
				?>
				"</select>
				</div>
			</li>


			
			<li id="li_1" >
				<h2>Turn off display after ... minutes</h2>
				<p>
				Please note: Depending on the display, the screen goes black but the backlight remains on.
				</p>
				<div>
					<output id="rangeval" class="rangeval"><?php 
						echo $data["timeout"]["idleDisplayOff"]
				?> min</output>				
				<input class="range slider-progress" name="idleDisplayOff" type="range" min="0" max="120" step="1.0" value="<?php 
					echo $data["timeout"]["idleDisplayOff"]
				?>" oninput="this.previousElementSibling.value = this.value + ' min'">
				</div>
			</li>

			<li id="li_1" >
				<h2>Display resolution X in pixel</h2>
				<div>
				<input id="resX" name="resX" class="element text medium" type="number" maxlength="255" value="<?php
				print $data["chromium"]["resX"];
				?>"/>
				</div>
			</li>
			<li id="li_1" >
				<h2>Display resolution Y in pixel</h2>
				<div>
				<input id="resY" name="resY" class="element text medium" type="number" maxlength="255" value="<?php
				print $data["chromium"]["resY"];
				?>"/>
				</div>
			</li>

			<li class="buttons">
				<input type="hidden" name="form_id" value="37271" />

				<input id="saveForm" class="button_text" type="submit" name="displayset" value="Submit" />
			</li>
		</ul>
	</details>
	<details>
		<summary><i class="fa-solid fa-volume-high"></i> Audio settings</summary>
		<ul>
			<li id="li_1" >
				<h2>Audio device / Soundcard </h2>
				<div>
				<select id="audio" name="audio" class="element text medium">
				<?php
				$audio = $data["mupibox"]["AudioDevices"];
				foreach($audio as $key) {
				if( $key['tname'] == $data["mupibox"]["physicalDevice"] )
				{
				$selected = " selected=\"selected\"";
				}
				else
				{
				$selected = "";
				}
				print "<option value=\"". $key['tname'] . "\"" . $selected  . ">" . $key['ufname'] . "</option>";
				}
				?>
				"</select>
				</div>
			</li>
			<li id="li_1" >
				<h2>Volume (in 5% Steps)</h2>
				<div>
				<p><b>PLEASE NOTE:</b> If you adjust the volume here, the volume indicator on the display will not be updated!</p>
					<output id="rangeval" class="rangeval"><?php 
					$command = "sudo su dietpi -c '/usr/bin/amixer sget Master | grep \"Right:\" | cut -d\" \" -f7 | sed \"s/\\[//g\" | sed \"s/\\]//g\" | sed \"s/\%//g\"'";
					$VolumeNow = exec($command, $voutput);
					echo $voutput[0];
				?>%</output>				

				<input class="range slider-progress" name="thisvolume" type="range" min="0" max="100" step="5.0" value="<?php 
					echo $voutput[0];
				?>"list="steplist" oninput="this.previousElementSibling.value = this.value + '%'">
				</div>

			</li>
			<li id="li_1" >
				<h2>Volume after power on </h2>
				<div>
				<output id="rangeval" class="rangeval"><?php 
					echo $data["mupibox"]["startVolume"];
				?>%</output>
				<input class="range slider-progress" name="volume" type="range" min="0" max="100" step="5.0" value="<?php 
					echo $data["mupibox"]["startVolume"];
				?>"list="steplist" oninput="this.previousElementSibling.value = this.value + '%'">
				</div>
			</li>

			<li id="li_1" >
				<h2>Set max volume</h2>
				<div>

				<output id="rangeval" class="rangeval"><?php 
					echo $data["mupibox"]["maxVolume"];
				?>%</output>
				<input class="range slider-progress" name="maxVolume" type="range" min="0" max="100" step="5.0" value="<?php 
					echo $data["mupibox"]["maxVolume"];
				?>"list="steplist" oninput="this.previousElementSibling.value = this.value + '%'">

				</div>
			</li>			
			

			<li class="buttons">
				<input type="hidden" name="form_id" value="37271" />

				<input id="saveForm" class="button_text" type="submit" name="audioset" value="Submit" />
			</li>
		</ul>
	</details>
	<details>
		<summary><i class="fa-solid fa-power-off"></i> Power-on settings</summary>
		<ul>
		
			<li id="li_1" >
				<h2>Power-Off Button delay </h2>
				<p>Waiting time (in seconds) until the button is pressed as a shutdown indicator
				</p>
				<div>
					<output id="rangeval" class="rangeval"><?php 
					echo $data["timeout"]["pressDelay"];
				?> sec</output>				

				<input class="range slider-progress" name="pressDelay" type="range" min="0" max="5" step="1.0" value="<?php 
					echo $data["timeout"]["pressDelay"];
				?>" oninput="this.previousElementSibling.value = this.value + ' sec'"><output></output>
				</div>
			</li>

			<li id="li_1" >
				<h2>LED GPIO OnOffShim </h2>
				<p>Possible standard GPIO-Pins are 4, 17, 18, 21, 22, 23, 24, 25 (default PIN) and 27. GPIOs 4 and 17 are used by OnOffShim. GPIOs 18 and 21 are used by HifiBerry MiniAmp. Just use free GPIOs to avoid system errors.</p>
				<div><select id="ledPin" name="ledPin" class="element text small">
				
				<?php
				$leds = array( "4", "17", "18", "21", "22", "23", "24", "25", "27" );
				foreach($leds as $pin) {
				if( $pin == $data["shim"]["ledPin"] )
				{
				$selected = " selected=\"selected\"";
				}
				else
				{
				$selected = "";
				}
				print "<option value=\"". $pin . "\"" . $selected  . ">" . $pin . "</option>";
				}
				?>
				"</select>	
				</div>
			</li>

			<li id="li_1" >
				<h2>LED Brightness normal (from 0 to 100%)</h2>
				<div>
					<output id="rangeval" class="rangeval"><?php 
					echo $data["shim"]["ledBrightnessMax"]
				?></output>				
				
				<input class="range slider-progress" name="ledmaxbrightness" type="range" min="0" max="100" step="1.0" value="<?php 
					echo $data["shim"]["ledBrightnessMax"]
				?>" oninput="this.previousElementSibling.value = this.value">
				</div>
			</li>

			<li id="li_1" >
				<h2>LED Brightness dimmed (from 0 to 100%)</h2>
				<div>
					<output id="rangeval" class="rangeval"><?php 
					echo $data["shim"]["ledBrightnessMin"]
				?></output>				
				<input class="range slider-progress" name="ledminbrightness" type="range" min="0" max="100" step="1.0" value="<?php 
					echo $data["shim"]["ledBrightnessMin"]
				?>" oninput="this.previousElementSibling.value = this.value">
				</div>
			</li>

			<li class="buttons">
				<input type="hidden" name="form_id" value="37271" />

				<input id="saveForm" class="button_text" type="submit" name="powerset" value="Submit" />
			</li>
		</ul>
	</details>
</form><p>
<?php
 include ('includes/footer.php');
?>

<?php
        //$time2sleep=readfile("/tmp/.time2sleep");
        $time2sleep=fgets(fopen("/tmp/.time2sleep", 'r'));
?>


<script>

for (let e of document.querySelectorAll('input[type="range"].slider-progress')) {
  e.style.setProperty('--value', e.value);
  e.style.setProperty('--min', e.min == '' ? '0' : e.min);
  e.style.setProperty('--max', e.max == '' ? '100' : e.max);
  e.addEventListener('input', () => e.style.setProperty('--value', e.value));
}

// Credit: Mateusz Rybczonec

const FULL_DASH_ARRAY = 283;
const WARNING_THRESHOLD = 10;
const ALERT_THRESHOLD = 5;

const COLOR_CODES = {
  info: {
    color: "green"
  },
  warning: {
    color: "orange",
    threshold: WARNING_THRESHOLD
  },
  alert: {
    color: "red",
    threshold: ALERT_THRESHOLD
  }
};

const TIME_LIMIT = <?php 
if( $time2sleep )
	{
	echo $time2sleep;
	}
else
	{
	echo "0";
	}
?>;
let timePassed = 0;
let timeLeft = TIME_LIMIT;
let timerInterval = null;
let remainingPathColor = COLOR_CODES.info.color;

document.getElementById("app").innerHTML = `
<div class="base-timer">
  <svg class="base-timer__svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
    <g class="base-timer__circle">
      <circle class="base-timer__path-elapsed" cx="50" cy="50" r="45"></circle>
      <path
        id="base-timer-path-remaining"
        stroke-dasharray="283"
        class="base-timer__path-remaining ${remainingPathColor}"
        d="
          M 50, 50
          m -45, 0
          a 45,45 0 1,0 90,0
          a 45,45 0 1,0 -90,0
        "
      ></path>
    </g>
  </svg>
  <span id="base-timer-label" class="base-timer__label">${formatTime(
    timeLeft
  )}</span>
</div>
	`;

startTimer();

function onTimesUp() {
  clearInterval(timerInterval);
}

function startTimer() {
  timerInterval = setInterval(() => {
    timePassed = timePassed += 1;
    timeLeft = TIME_LIMIT - timePassed;
    document.getElementById("base-timer-label").innerHTML = formatTime(
      timeLeft
    );
    setCircleDasharray();
    setRemainingPathColor(timeLeft);

    if (timeLeft === 0) {
      onTimesUp();
    }
  }, 1000);
}

function formatTime(time) {
  const hours = Math.floor(time / 60 / 60);
  minutes = Math.floor(time / 60 - hours * 60);
  let seconds = time % 60;

  if (seconds < 10) {
    seconds = `0${seconds}`;
  }
  if (minutes < 10) {
    minutes = `0${minutes}`;
  }


  return `${hours}:${minutes}:${seconds}`;
}

function setRemainingPathColor(timeLeft) {
  const { alert, warning, info } = COLOR_CODES;
  if (timeLeft <= alert.threshold) {
    document
      .getElementById("base-timer-path-remaining")
      .classList.remove(warning.color);
    document
      .getElementById("base-timer-path-remaining")
      .classList.add(alert.color);
  } else if (timeLeft <= warning.threshold) {
    document
      .getElementById("base-timer-path-remaining")
      .classList.remove(info.color);
    document
      .getElementById("base-timer-path-remaining")
      .classList.add(warning.color);
  }
}

function calculateTimeFraction() {
  const rawTimeFraction = timeLeft / TIME_LIMIT;
  return rawTimeFraction - (1 / TIME_LIMIT) * (1 - rawTimeFraction);
}

function setCircleDasharray() {
  const circleDasharray = `${(
    calculateTimeFraction() * FULL_DASH_ARRAY
  ).toFixed(0)} 283`;
  document
    .getElementById("base-timer-path-remaining")
    .setAttribute("stroke-dasharray", circleDasharray);
}

</script>

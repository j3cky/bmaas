#---------------UpdateDVSLacpGroupConfig_Task---------------

$lacpGroupSpec = New-Object VMware.Vim.VMwareDvsLacpGroupSpec[] (1)

$lacpGroupSpec[0] = New-Object VMware.Vim.VMwareDvsLacpGroupSpec

$lacpGroupSpec[0].LacpGroupConfig = New-Object VMware.Vim.VMwareDvsLacpGroupConfig

$lacpGroupSpec[0].LacpGroupConfig.Mode = 'passive'

$lacpGroupSpec[0].LacpGroupConfig.Ipfix = New-Object VMware.Vim.VMwareDvsLagIpfixConfig

$lacpGroupSpec[0].LacpGroupConfig.LoadbalanceAlgorithm = 'srcDestIpTcpUdpPortVlan'

$lacpGroupSpec[0].LacpGroupConfig.Vlan = New-Object VMware.Vim.VMwareDvsLagVlanConfig

$lacpGroupSpec[0].LacpGroupConfig.Name = 'lag1'
$lacpGroupSpec[0].LacpGroupConfig.UplinkNum = 2

$lacpGroupSpec[0].Operation = 'add'

#$_this = Get-View -Id 'VmwareDistributedVirtualSwitch-dvs-1194'

$_this = Get-VDSwitch "DSwitch 1" | Get-View

$_this.UpdateDVSLacpGroupConfig_Task($lacpGroupSpec)


#----------------- End of code capture -----------------

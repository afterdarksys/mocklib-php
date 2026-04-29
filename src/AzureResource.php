<?php

namespace MockFactory;

class AzureResource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // ========================================================================
    // Resource Groups
    // ========================================================================

    public function listResourceGroups(string $subscriptionId, array $params = []): array
    {
        $query = [];
        if (isset($params['filter'])) {
            $query['$filter'] = $params['filter'];
        }

        $response = $this->client->get(
            "/azure/subscriptions/{$subscriptionId}/resourceGroups",
            $query
        );

        return $response['value'] ?? $response ?? [];
    }

    public function createResourceGroup(string $subscriptionId, string $resourceGroupName, array $params): array
    {
        $body = [
            'location' => $params['location'],
        ];

        if (isset($params['tags'])) {
            $body['tags'] = $params['tags'];
        }

        return $this->client->request(
            'PUT',
            "/azure/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroupName}",
            $body
        );
    }

    // ========================================================================
    // Virtual Networks
    // ========================================================================

    public function createVNet(string $subscriptionId, string $resourceGroup, string $vnetName, array $params): array
    {
        $body = [
            'location'   => $params['location'],
            'properties' => [
                'addressSpace' => [
                    'addressPrefixes' => $params['address_prefixes'] ?? ['10.0.0.0/16'],
                ],
            ],
        ];

        if (isset($params['tags'])) {
            $body['tags'] = $params['tags'];
        }

        if (isset($params['dns_servers'])) {
            $body['properties']['dhcpOptions'] = ['dnsServers' => $params['dns_servers']];
        }

        return $this->client->request(
            'PUT',
            $this->vnetPath($subscriptionId, $resourceGroup, $vnetName),
            $body
        );
    }

    public function listVNets(string $subscriptionId, string $resourceGroup): array
    {
        $response = $this->client->get(
            "/azure/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/virtualNetworks"
        );

        return $response['value'] ?? $response ?? [];
    }

    public function getVNet(string $subscriptionId, string $resourceGroup, string $vnetName): array
    {
        return $this->client->get($this->vnetPath($subscriptionId, $resourceGroup, $vnetName));
    }

    public function deleteVNet(string $subscriptionId, string $resourceGroup, string $vnetName): array
    {
        return $this->client->delete($this->vnetPath($subscriptionId, $resourceGroup, $vnetName));
    }

    // ========================================================================
    // Subnets
    // ========================================================================

    public function createSubnet(string $subscriptionId, string $resourceGroup, string $vnetName, string $subnetName, array $params): array
    {
        $body = [
            'properties' => [
                'addressPrefix' => $params['address_prefix'],
            ],
        ];

        if (isset($params['network_security_group_id'])) {
            $body['properties']['networkSecurityGroup'] = ['id' => $params['network_security_group_id']];
        }

        if (isset($params['route_table_id'])) {
            $body['properties']['routeTable'] = ['id' => $params['route_table_id']];
        }

        return $this->client->request(
            'PUT',
            $this->vnetPath($subscriptionId, $resourceGroup, $vnetName) . "/subnets/{$subnetName}",
            $body
        );
    }

    // ========================================================================
    // Network Security Groups
    // ========================================================================

    public function createNSG(string $subscriptionId, string $resourceGroup, string $nsgName, array $params): array
    {
        $body = [
            'location'   => $params['location'],
            'properties' => [],
        ];

        if (isset($params['tags'])) {
            $body['tags'] = $params['tags'];
        }

        return $this->client->request(
            'PUT',
            $this->nsgPath($subscriptionId, $resourceGroup, $nsgName),
            $body
        );
    }

    public function getNSG(string $subscriptionId, string $resourceGroup, string $nsgName): array
    {
        return $this->client->get($this->nsgPath($subscriptionId, $resourceGroup, $nsgName));
    }

    public function createSecurityRule(string $subscriptionId, string $resourceGroup, string $nsgName, string $ruleName, array $params): array
    {
        $body = [
            'properties' => [
                'protocol'                   => $params['protocol'] ?? 'Tcp',
                'sourcePortRange'            => $params['source_port_range'] ?? '*',
                'destinationPortRange'       => $params['destination_port_range'],
                'sourceAddressPrefix'        => $params['source_address_prefix'] ?? '*',
                'destinationAddressPrefix'   => $params['destination_address_prefix'] ?? '*',
                'access'                     => $params['access'] ?? 'Allow',
                'priority'                   => $params['priority'] ?? 100,
                'direction'                  => $params['direction'] ?? 'Inbound',
            ],
        ];

        return $this->client->request(
            'PUT',
            $this->nsgPath($subscriptionId, $resourceGroup, $nsgName) . "/securityRules/{$ruleName}",
            $body
        );
    }

    // ========================================================================
    // Network Interfaces
    // ========================================================================

    public function createNIC(string $subscriptionId, string $resourceGroup, string $nicName, array $params): array
    {
        $body = [
            'location'   => $params['location'],
            'properties' => [
                'ipConfigurations' => $params['ip_configurations'] ?? [],
            ],
        ];

        if (isset($params['network_security_group_id'])) {
            $body['properties']['networkSecurityGroup'] = ['id' => $params['network_security_group_id']];
        }

        if (isset($params['tags'])) {
            $body['tags'] = $params['tags'];
        }

        return $this->client->request(
            'PUT',
            "/azure/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/networkInterfaces/{$nicName}",
            $body
        );
    }

    // ========================================================================
    // Public IP Addresses
    // ========================================================================

    public function createPublicIP(string $subscriptionId, string $resourceGroup, string $publicIpName, array $params): array
    {
        $body = [
            'location'   => $params['location'],
            'sku'        => ['name' => $params['sku'] ?? 'Standard'],
            'properties' => [
                'publicIPAllocationMethod' => $params['allocation_method'] ?? 'Static',
            ],
        ];

        if (isset($params['dns_label'])) {
            $body['properties']['dnsSettings'] = ['domainNameLabel' => $params['dns_label']];
        }

        if (isset($params['tags'])) {
            $body['tags'] = $params['tags'];
        }

        return $this->client->request(
            'PUT',
            "/azure/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/publicIPAddresses/{$publicIpName}",
            $body
        );
    }

    // ========================================================================
    // Managed Disks
    // ========================================================================

    public function createDisk(string $subscriptionId, string $resourceGroup, string $diskName, array $params): array
    {
        $body = [
            'location'   => $params['location'],
            'sku'        => ['name' => $params['sku'] ?? 'Premium_LRS'],
            'properties' => [
                'diskSizeGB'      => $params['disk_size_gb'] ?? 128,
                'creationData'    => [
                    'createOption' => $params['create_option'] ?? 'Empty',
                ],
            ],
        ];

        if (isset($params['tags'])) {
            $body['tags'] = $params['tags'];
        }

        return $this->client->request(
            'PUT',
            $this->diskPath($subscriptionId, $resourceGroup, $diskName),
            $body
        );
    }

    public function listDisks(string $subscriptionId, string $resourceGroup): array
    {
        $response = $this->client->get(
            "/azure/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/disks"
        );

        return $response['value'] ?? $response ?? [];
    }

    public function getDisk(string $subscriptionId, string $resourceGroup, string $diskName): array
    {
        return $this->client->get($this->diskPath($subscriptionId, $resourceGroup, $diskName));
    }

    public function deleteDisk(string $subscriptionId, string $resourceGroup, string $diskName): array
    {
        return $this->client->delete($this->diskPath($subscriptionId, $resourceGroup, $diskName));
    }

    // ========================================================================
    // Virtual Machines
    // ========================================================================

    public function createVM(string $subscriptionId, string $resourceGroup, string $vmName, array $params): array
    {
        $body = [
            'location'   => $params['location'],
            'properties' => [
                'hardwareProfile' => [
                    'vmSize' => $params['vm_size'] ?? 'Standard_DS1_v2',
                ],
                'storageProfile' => $params['storage_profile'] ?? [],
                'osProfile'      => $params['os_profile'] ?? [],
                'networkProfile' => $params['network_profile'] ?? [],
            ],
        ];

        if (isset($params['tags'])) {
            $body['tags'] = $params['tags'];
        }

        if (isset($params['zones'])) {
            $body['zones'] = $params['zones'];
        }

        return $this->client->request(
            'PUT',
            $this->vmPath($subscriptionId, $resourceGroup, $vmName),
            $body
        );
    }

    public function listVMs(string $subscriptionId, string $resourceGroup): array
    {
        $response = $this->client->get(
            "/azure/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines"
        );

        return $response['value'] ?? $response ?? [];
    }

    public function getVM(string $subscriptionId, string $resourceGroup, string $vmName): array
    {
        return $this->client->get($this->vmPath($subscriptionId, $resourceGroup, $vmName));
    }

    public function deleteVM(string $subscriptionId, string $resourceGroup, string $vmName): array
    {
        return $this->client->delete($this->vmPath($subscriptionId, $resourceGroup, $vmName));
    }

    public function startVM(string $subscriptionId, string $resourceGroup, string $vmName): array
    {
        return $this->client->post(
            $this->vmPath($subscriptionId, $resourceGroup, $vmName) . '/start',
            []
        );
    }

    public function stopVM(string $subscriptionId, string $resourceGroup, string $vmName): array
    {
        return $this->client->post(
            $this->vmPath($subscriptionId, $resourceGroup, $vmName) . '/powerOff',
            []
        );
    }

    public function deallocateVM(string $subscriptionId, string $resourceGroup, string $vmName): array
    {
        return $this->client->post(
            $this->vmPath($subscriptionId, $resourceGroup, $vmName) . '/deallocate',
            []
        );
    }

    public function restartVM(string $subscriptionId, string $resourceGroup, string $vmName): array
    {
        return $this->client->post(
            $this->vmPath($subscriptionId, $resourceGroup, $vmName) . '/restart',
            []
        );
    }

    // ========================================================================
    // Private path helpers
    // ========================================================================

    private function vnetPath(string $sub, string $rg, string $vnet): string
    {
        return "/azure/subscriptions/{$sub}/resourceGroups/{$rg}/providers/Microsoft.Network/virtualNetworks/{$vnet}";
    }

    private function nsgPath(string $sub, string $rg, string $nsg): string
    {
        return "/azure/subscriptions/{$sub}/resourceGroups/{$rg}/providers/Microsoft.Network/networkSecurityGroups/{$nsg}";
    }

    private function diskPath(string $sub, string $rg, string $disk): string
    {
        return "/azure/subscriptions/{$sub}/resourceGroups/{$rg}/providers/Microsoft.Compute/disks/{$disk}";
    }

    private function vmPath(string $sub, string $rg, string $vm): string
    {
        return "/azure/subscriptions/{$sub}/resourceGroups/{$rg}/providers/Microsoft.Compute/virtualMachines/{$vm}";
    }
}

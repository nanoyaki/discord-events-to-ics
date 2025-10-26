{
  description = "A very basic flake";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
    flake-parts.url = "github:hercules-ci/flake-parts";
    systems.url = "github:nix-systems/default-linux";
  };

  outputs =
    inputs:

    inputs.flake-parts.lib.mkFlake { inherit inputs; } {
      perSystem =
        { pkgs, ... }:

        {
          devShells.default = pkgs.mkShell {
            buildInputs = with pkgs; [
              php84
              php84.packages.composer
            ];
          };

          packages.default = pkgs.callPackage (
            {
              php84,
            }:

            php84.buildComposerProject2 {
              pname = "discord-events-to-ics";
              version = "1.1.2";

              src = ./.;

              composerNoPlugins = false;
              composerLock = ./composer.lock;
              vendorHash = "sha256-gc/8lTgaMdsBqjyx9bHUDTILufgKa4jyWR6HuhULOX8=";
            }
          ) { };
        };

      systems = import inputs.systems;
    };
}

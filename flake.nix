{
  description = "A very basic flake";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
  };

  outputs =
    { nixpkgs, ... }:
    let
      pkgs = nixpkgs.legacyPackages.x86_64-linux;
    in
    {
      devShells.x86_64-linux.default = pkgs.mkShell {
        buildInputs = with pkgs; [
          php84
          php84.packages.composer
        ];
      };

      packages.x86_64-linux.default = pkgs.callPackage (
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
}

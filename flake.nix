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
      devShells.x86_64-linux.default = pkgs.mkShell (
        let
          php = (
            pkgs.php84.buildEnv {
              extensions = ({ enabled, all }: enabled ++ (with all; [ mongodb ]));
            }
          );
        in
        {
          buildInputs = [
            php
            php.packages.composer
          ];
        }
      );

      packages.x86_64-linux.default = pkgs.callPackage ./nix/package.nix { };
      apps.x86_64-linux.default = pkgs.callPackage ./nix/package.nix { };
    };
}
